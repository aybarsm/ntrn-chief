<?php

namespace App\Commands\Customised;

use App\Traits\Configurable;
use Illuminate\Console\Application as Artisan;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use function Illuminate\Filesystem\join_paths;

class AppBuild extends Command implements SignalableCommandInterface
{
    use Configurable;

    protected $signature = 'app:build
    {--timeout=300 : The timeout in seconds or 0 to disable}
    {--b|box=* : Extra options to pass to Box}
    {--dry-run : Do not execute the command}';

    protected $description = 'Build a single file executable (Customised)';

    private OutputInterface $originalOutput;

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


//        $this->title('Building process');
//        $this->build();
    }

//    private function isDryRun(): bool
//    {
//        return $this->option('dry-run');
//    }
//
//    private function nextTask(string $name, string $message): void
//    {
//        $slug = Str::slug($name, '_');
//        $tasks = $this->config()->set("tasks.{$slug}", $this->config()->get("tasks.{$slug}", -1) + 1);
//
//        $taskId = count($tasks) . '.' . ($tasks[$slug] > 0 ? $tasks[$slug] . '.' : '');
//        $this->task(sprintf('   %s <fg=yellow>%s</> %s', $taskId, $name, $message));
//    }
//
//    private function prepare(): AppBuild
//    {
//        $this->nextTask('Prepare', 'Prepare the build environment');
//
//        if (! $this->isDryRun()) {
//            File::ensureDirectoryExists($this->config()->get('build.path'));
//            File::put(config('dev.build.app_version'), $this->config()->get('version'));
//        }
//
//        return $this;
//    }
//
//    private function cleanUp(): AppBuild
//    {
//        $this->nextTask('Clean Up', 'Clean up the build environment');
//
//        if (! $this->isDryRun()) {
//            if (File::exists(config('dev.build.app_version'))) {
//                File::delete(config('dev.build.app_version'));
//            }
//        }
//
//        return $this;
//    }
//
//    private function build(): void
//    {
//        $exception = null;
//
//        try {
//            $this->prepare()->compile()->binaries();
//        } catch (\Throwable $exception) {
//            //
//        }
//
//        $this->cleanUp();
//
//        if ($exception !== null) {
//            throw $exception;
//        }
//    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        return parent::run($input, $this->originalOutput = $output);
    }

    private function compile(): AppBuild
    {
        $this->nextTask('Compile', 'Generate single .phar file');

        if ($this->isDryRun()){
            return $this;
        }

        $process = new Process(
            command: [$this->config()->get('box.binary'), 'compile'] + $this->getBoxOptions(),
            env: null,
            input: null,
            timeout: $this->getTimeout()
        );

        /** @phpstan-ignore-next-line This is an instance of `ConsoleOutputInterface` */
        $section = tap($this->originalOutput->section())->write('');

        $progressBar = new ProgressBar(
            output: $this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL ? new NullOutput : $section,
            max: 25
        );

        $progressBar->setProgressCharacter("\xF0\x9F\x8D\xBA");

        $process->start();

        foreach ($process as $type => $data) {
            $progressBar->advance();

            if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
                $process::OUT === $type ? $this->info("$data") : $this->error("$data");
            }
        }

        $progressBar->finish();

        $section->clear();

        $this->output->newLine();

        if (! File::exists($this->config()->get('build.initial'))) {
            throw new \RuntimeException('Failed to compile the application.');
        }else{
            File::move($this->config()->get('build.initial'), $this->config()->get('build.phar'));
            File::chmod($this->config()->get('build.phar'), config('dev.build.chmod'));

            $this->output->writeln(
                sprintf('    Compiled successfully: <fg=green>%s</>', $this->config()->get('build.phar'))
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
            if ($this->prepareSfx($binary)){

            }
        }

        return $this;
    }

    private function remoteFileSize(string $url): int|false
    {
        $response = Http::head($url);

        if (! $response->header('Content-Length')) {
            return false;
        }

        return (int)$response->header('Content-Length');
    }

    private function prepareSfx(array $binary): bool
    {
        $this->nextTask('Binary', "Prepare SFX for {$binary['target']}");

        $fileSize = $this->remoteFileSize($binary['sfx']['remote']);

        if ($fileSize === false) {
            $this->error("Failed to query the SFX file size on prepare stage: {$binary['sfx']['remote']}");
            return false;
        }

        $binary['sfx']['fileSize'] = $fileSize;

        if (File::exists($binary['sfx']['local'])) {
            if (File::size($binary['sfx']['local']) == $fileSize){
                $this->info("SFX file is ready: {$binary['sfx']['local']}");
                return true;
            }

            $this->info("SFX file size mismatch, will be downloaded: {$binary['sfx']['local']}");
            if (! $this->isDryRun()){
                File::delete($binary['sfx']['local']);
            }
        }

    }

    private function downloadSfx(array $binary): bool
    {
        $this->nextTask('Binary', "Download SFX for {$binary['target']}");

        if (! isset($binary['sfx']['fileSize'])) {
            $fileSize = $this->remoteFileSize($binary['sfx']['remote']);
            if ($fileSize === false) {
                $this->error("Failed to query the SFX file size on download stage: {$binary['sfx']['remote']}");
                return false;
            }

            $binary['sfx']['fileSize'] = $fileSize;
        }

        /** @phpstan-ignore-next-line This is an instance of `ConsoleOutputInterface` */
        $section = tap($this->originalOutput->section())->write('');

        $progressBar = new ProgressBar(
            output: $this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL ? new NullOutput : $section,
            max: (int)$fileSize
        );

        $progressBar->start();

        $fileHandle = fopen($binary['sfx']['local'], 'w');

        Http::withOptions(['stream' => true])
            ->get($binary['sfx']['remote'])
            ->onBodyChunk(function ($chunk) use ($fileHandle, $progressBar) {
                fwrite($fileHandle, $chunk);
                $progressBar->advance(strlen($chunk));
            });
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
