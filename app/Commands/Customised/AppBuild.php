<?php

namespace App\Commands\Customised;

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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process as SymfonyProcess;
use function Illuminate\Filesystem\join_paths;

class AppBuild extends Command implements SignalableCommandInterface
{
    use Configurable;

    protected $signature = 'app:build
    {--timeout=300 : The timeout in seconds or 0 to disable}
    {--b|box=* : Extra options to pass to Box}
    {--dry-run : Do not execute the command}';

    protected $description = 'Build a single file executable (Customised)';

    public function handle(): void
    {
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

        $binaries = [];
        foreach (config('dev.build.distributions', []) as $distribution => $sfx) {
            $binaries[$distribution] = [
                'target' => $distribution,
                'output' => join_paths($this->config()->get('build.path'), "{$this->config()->get('name')}-{$distribution}"),
                'sfx' => [
                    'name' => $sfx,
                    'local' => join_paths(config('dev.build.sfx.path'), $sfx),
                    'remote' => Str::of(config('dev.build.sfx.url'))->trim()->finish('/')->append($sfx)->value(),
                ],
            ];
        }

        $this->config()->set("build.binaries", $binaries);

        $this->config()->set('box', [
            'binary' => join_paths(base_path(), 'vendor', 'laravel-zero', 'framework', 'bin', (windows_os() ? 'box.bat' : 'box')),
        ]);

        $this->config()->set('tasks', []);

        $this->title('Building process');
        $this->build();
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

        $process = new SymfonyProcess(
            command: [$this->config()->get('box.binary'), 'compile'] + $this->getBoxOptions(),
            env: null,
            input: null,
            timeout: $this->getTimeout()
        );

        $progressBar = $this->output->createProgressBar();

        $process->start();

        foreach ($process as $type => $data) {
            $progressBar->advance();
        }

        $progressBar->finish();
        $progressBar->clear();

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
                $result = Process::run("cat {$binary['sfx']['local']} {$this->config()->get('build.phar')} > {$binary['output']}");

                if ($result->successful()) {
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

    private function cleanUp(): AppBuild
    {
        if (File::exists(config('dev.build.app_version'))) {
            File::delete(config('dev.build.app_version'));
        }

        return $this;
    }

    public function getSubscribedSignals(): array
    {
        if (defined('SIGINT')) {
            return [\SIGINT];
        }

        return [];
    }
    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        if (defined('SIGINT') && $signal === \SIGINT) {
            $this->cleanUp();
        }

        return self::SUCCESS;
    }

    public function __destruct()
    {
        $this->cleanUp();
    }
}
