<?php

namespace App\Commands\Customised;

use App\Traits\Command\SignalHandler;
use App\Traits\Configurable;
use Illuminate\Console\Application as Artisan;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Process\Process as SymfonyProcess;
use function Illuminate\Filesystem\join_paths;

class AppBuild extends Command implements SignalableCommandInterface
{
    use Configurable, SignalHandler;

    protected $signature = 'app:build
    {--timeout=300 : The timeout in seconds or 0 to disable}
    {--b|box=* : Extra options to pass to Box}
    {--dry-run : Do not execute the command}';

    protected $description = 'Build a single file executable (Customised)';

    public function handle(): void
    {
        $this->setSignalHandler('SIGINT', function (...$params) {
            $this->cleanUp(true);
        });

        $this->config()->set('ts.instance', Carbon::now('UTC'));
        $this->config()->set('ts.safe', $this->config()->get('ts.instance')->format('Ymd\THis\Z'));
        $this->config()->set('name', Str::lower(config('app.name')));
        $this->config()->set('version', config('app.version'));

        if ($this->config()->get('version') === 'unreleased') {
            $this->error('App has not released yet.');
            return;
        }

        $this->config()->set('build', [
            'initial' => $this->app->basePath($this->getBinary()).'.phar',
            'id' => ($buildId = ($this->config()->get('version') . '-' . $this->config()->get('ts.safe'))),
            'path' => ($buildPath = $this->app->buildsPath($buildId)),
            'phar' => join_paths($buildPath, "{$this->config()->get('name')}.phar"),
        ]);

        $spcPath = config('dev.build.spc.path');
        $spcUrl = Str::of(config('dev.build.spc.url'))->trim()->finish('/');
        $spcFileNamePattern = config('dev.build.spc.fileNamePattern');

        $binaries = [];
        foreach (config('dev.build.spc.distributions', []) as $distribution => $sfx) {
            $sfxLocalName = Str::of($sfx)->match($spcFileNamePattern)->finish('.sfx')->value();
            $sfxUrl = $spcUrl->finish($sfx)->value();

            $binaries[$distribution] = [
                'target' => $distribution,
                'output' => join_paths($this->config()->get('build.path'), "{$this->config()->get('name')}-{$distribution}"),
                'spc' => [
                    'name' => $sfx,
                    'local' => join_paths($spcPath, $sfxLocalName),
                    'remote' => $spcUrl->finish($sfx)->value(),
                ],
            ];
        }

        $this->config()->set("build.binaries", $binaries);

        $this->config()->set('box', [
            'binary' => join_paths(base_path(), 'vendor', 'laravel-zero', 'framework', 'bin', (windows_os() ? 'box.bat' : 'box')),
        ]);

        $this->config()->set('tasks', []);

        $this->title('Building process');
        dump($this->config()->full());
//        $this->build();
    }



    private function nextTask(string $name, string $message): void
    {
        $slug = Str::slug($name, '_');
        $tasks = $this->config()->set("tasks.{$slug}", $this->config()->get("tasks.{$slug}", -1) + 1);

        $taskId = count($tasks) . '.' . ($tasks[$slug] > 0 ? $tasks[$slug] . '.' : '');

        $this->task(sprintf('%s <fg=yellow>%s</> %s', $taskId, $name, $message));
    }

    private function prepare(): AppBuild
    {
        $this->nextTask('Prepare', 'Prepare the build environment');

        if (! $this->isDryRun()) {
            File::ensureDirectoryExists($this->config()->get('build.path'));
            File::put(config('dev.build.app_version'), $this->config()->get('version'));
        }

        return $this;
    }

    private function build(): void
    {
        $exception = null;

        try {
            $this->prepare()->compile()->binaries();
        } catch (\Throwable $exception) {
            //
        }

        $this->cleanUp();

        if ($exception !== null) {
            throw $exception;
        }
    }

    private function compile(): AppBuild
    {
        $this->nextTask('Compile', 'Generate single .phar file');

        if ($this->isDryRun()){
            return $this;
        }

        $this->withSpinner(1, function($spinner) {
            $process = Process::timeout($this->getTimeout())
                ->command([$this->config()->get('box.binary'), 'compile'] + $this->getBoxOptions())
                ->start();

            while($process->running()) {
                $spinner->setMaxSteps($spinner->getMaxSteps() + 1);
                $spinner->advance();
                usleep(5000);
            }

            $result = $process->wait();
            $message = $result->successful() ? ['SUCCESS', 'completed successfully'] : ['ERROR', "failed with exit code {$result->exitCode()}"];
            $message = sprintf("!%s! Building %s.", ...$message);

            $spinner->setMessage($message);
        }, 'Building a single .phar file...');

        $this->output->newLine();

        if (! File::exists($this->config()->get('build.initial'))) {
            throw new \RuntimeException('Failed to compile the application.');
        }else{
            File::move($this->config()->get('build.initial'), $this->config()->get('build.phar'));

            $this->output->writeln(
                sprintf('Compiled successfully: <fg=green>%s</>', $this->config()->get('build.phar'))
            );
        }

        return $this;
    }
    private function binaries(): AppBuild
    {
        if (blank(config('dev.build.distributions'))) {
            return $this;
        }

        $this->nextTask('Binary', 'Prepare the distribution binaries');

        foreach ($this->config()->get('build.binaries') as $binary) {
            if (File::exists($binary['output']) && ! config('dev.build.overwrite')) {
                $this->info("Binary already exists: {$binary['output']}");
                continue;
            }

            if ($this->prepareSfx($binary)){
                $this->nextTask('Binary', "Create binary for {$binary['target']}");

                $result = $this->withSpinner(1, function($spinner) use($binary) {
                    $process = Process::timeout($this->getTimeout())
                        ->start("cat {$binary['sfx']['local']} {$this->config()->get('build.phar')} > {$binary['output']}");

                    while($process->running()) {
                        $spinner->setMaxSteps($spinner->getMaxSteps() + 1);
                        $spinner->advance();
                        usleep(5000);
                    }

                    $result = $process->wait();
                    $message = $result->successful() ? ['SUCCESS', 'completed successfully'] : ['ERROR', "failed with exit code {$result->exitCode()}"];
                    $message = sprintf("!%s! Building binary for {$binary['target']} %s.", ...$message);

                    $spinner->setMessage($message);
                    return $result;
                }, "Building binary for {$binary['target']}");

                $this->output->newLine();

                if ($result->successful()) {
                    if (! blank($chmod = config('dev.build.chmod')) && is_string($chmod) && is_numeric($chmod) && strlen($chmod) === 4) {
                        File::chmod($binary['output'], octdec(config('dev.build.chmod')));
                    }

                    $this->info("Binary is ready: {$binary['output']}");
                    continue;
                }

                $this->error("Failed to create binary for {$binary['target']}");
            }
        }

        return $this;
    }

    private function prepareSfx(array $binary): bool
    {
        $this->nextTask('Binary', "Prepare SFX for {$binary['target']}");

        File::ensureDirectoryExists(config('dev.build.sfx.path'));

        if (File::exists($binary['sfx']['local'])) {
            $this->info("SFX file is ready: {$binary['sfx']['local']}");
            return true;
        }

        $this->nextTask('Binary', "Download SFX for {$binary['target']}");

        if ($this->isDryRun()){
            return true;
        }

        $this->info("Downloading SFX file: {$binary['sfx']['remote']}");

        $progressBar = $this->output->createProgressBar();

        Http::sink($binary['sfx']['local'])
        ->withOptions([
            'progress' => function ($dlSize, $dlCompleted) use($progressBar, $binary) {
                if ($progressBar->getMaxSteps() == 0 && $dlSize > 0){
                    $progressBar->setMaxSteps($dlSize);
                    $progressBar->start();
                }

                if ($progressBar->getMaxSteps() > 0){
                    if ($dlCompleted < $dlSize){
                        $progressBar->setProgress($dlCompleted);
                    }elseif ($progressBar->getProgress() < $progressBar->getMaxSteps() && $dlCompleted == $dlSize){
                        $progressBar->finish();
                        $progressBar->clear();
                        $this->info("SFX file is ready: {$binary['sfx']['local']}");
                    }
                }
            }
        ])
        ->get($binary['sfx']['remote']);

        return true;
    }

    private function getBinary(): string
    {
        return str_replace(["'", '"'], '', Artisan::artisanBinary());
    }

    private function getTimeout(): ?float
    {
        if (! is_numeric($this->option('timeout'))) {
            throw new \InvalidArgumentException('The timeout value must be a number.');
        }

        $timeout = (float) $this->option('timeout');

        return $timeout > 0 ? $timeout : null;
    }

    private function getBoxOptions(): array
    {
        $boxOptions = [];
        foreach($this->option('box') as $option){
            $option = Str::of($option)->trim();
            $boxOptions[$option->ltrim('-')->before('=')->value()] = $option->after('=')->value();
        }

        $boxOptions = array_merge($boxOptions, [
            'working-dir' => base_path(),
            'config' => base_path('box.json'),
        ]);

        if ($this->output->isDebug()) {
            $boxOptions['debug'] = '';
        }

        return array_values(Arr::map($boxOptions,
            fn($value, $key) => Str::of($key)->start('--')->unless(blank($value), fn ($str) => $str->append('=' . $value))->value()
        ));
    }

    private function isDryRun(): bool
    {
        return $this->option('dry-run');
    }

    private function cleanUp(bool $isSignal = false): AppBuild|int
    {
        if (File::exists(config('dev.build.app_version'))) {
            File::delete(config('dev.build.app_version'));
        }

        return $isSignal ? self::SUCCESS : $this;
    }

    public function __destruct()
    {
        $this->cleanUp();
    }
}
