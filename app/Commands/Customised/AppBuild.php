<?php

namespace App\Commands\Customised;

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
use function Laravel\Prompts\text;

class AppBuild extends Command implements SignalableCommandInterface
{
    protected $signature = 'app:build
    {--timeout=300 : The timeout in seconds or 0 to disable}
    {--b|box=* : Extra options to pass to Box}';

    protected $description = 'Build a single file executable (Customised)';
    private static Carbon $buildTimestamp;
    private static string $buildVersion;
    private static string $buildName;
    private static string $outputDir;
    private static string $buildUtilsDir;

    private static ?string $config = null;

    private static ?string $box = null;

    private OutputInterface $originalOutput;
    private array $tasks = [];

    public function handle(): void
    {
        self::$buildTimestamp = Carbon::now('UTC');
        self::$buildVersion = app('git.version');
        self::$outputDir = base_path('dev' . DIRECTORY_SEPARATOR . 'builds' . DIRECTORY_SEPARATOR . self::$buildVersion . '-' . self::$buildTimestamp->format('Ymd\THis\Z'));

        if (self::$buildVersion === 'unreleased'){
            $this->error('App has not released yet.');
            return;
        }

        self::$buildName = 'ntrn';
//        self::$composer = File::json(base_path('composer.json'));

        $this->title('Building process');

        $this->build(self::$buildName);
    }

    private function nextTask(string $name, string $message): void
    {
        $slug = Str::slug($name);
        $this->tasks[$slug] = (($this->tasks[$slug] ?? 0) + 1);

        $taskId = count($this->tasks) . '.' . ($this->tasks[$slug] > 1 ? '.' . $this->tasks[$slug] : '');

        $this->task(sprintf('   %s <fg=yellow>%s</> %s', $taskId, $name, $message));
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        return parent::run($input, $this->originalOutput = $output);
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
            if (self::$config !== null) {
                $this->clear();
            }
        }

        return self::SUCCESS;
    }

    private function build(string $name): void
    {
        $exception = null;

        try {
            $this->prepare()->compile($name);
        } catch (\Throwable $exception) {
            //
        }

        $this->clear();

        if ($exception !== null) {
            throw $exception;
        }

        $this->output->writeln(
            sprintf('    Compiled successfully: <fg=green>%s</>', $this->app->buildsPath($name))
        );
    }

    private function compile(string $name): AppBuild
    {
        $output = self::$outputDir . DIRECTORY_SEPARATOR . self::$buildName;
        $boxBinary = ['vendor', 'laravel-zero', 'framework', 'bin', (windows_os() ? 'box.bat' : 'box')];
        $boxBinary = base_path(implode(DIRECTORY_SEPARATOR, $boxBinary));

        $this->nextTask('Compile', 'Generate single .phar file.');

        $process = new Process(
            command: [$boxBinary, 'compile'] + $this->getBoxOptions(),
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

        $pharPath = $this->app->basePath($this->getBinary()).'.phar';

        if (! File::exists($pharPath)) {
            throw new \RuntimeException('Failed to compile the application.');
        }

        File::move($pharPath, "{$output}.phar");
        File::chmod("{$output}.phar", 0755);

        $this->nextTask('Binary', 'Generate binary files for ' . implode(', ', config('dev.build.distributions')) . '.');

        foreach(config('dev.build.distributions') as $platform => $sfx){
            $this->nextTask('Binary', "for {$platform}");
            $binary = "{$output}-{$platform}";

            if (! config('dev.build.overwrite') && File::exists($binary)) {
                $this->output->writeln(
                    sprintf('    Binary is ready: <fg=green>%s</>', $binary)
                );
                File::chmod($binary, config('dev.build.chmod'));
                continue;
            }

        }

        return $this;
    }

    private function prepareSfx(string $distribution, string $sfx): bool
    {
        $this->nextTask('Binary', "Prepare SFX for {$distribution}");

        $sfxLocal = base_path(implode(DIRECTORY_SEPARATOR, ['dev', 'utils', 'sfx', $sfx]));

        $sfxRemote = Str::of(config('dev.build.sfx.url'))->trim()->finish('/')->append($sfx)->value();
        $response = Http::head($sfxRemote);
        $fileSize = $response->header('Content-Length');

        if (File::exists($sfxLocal) && File::size($sfxLocal) == $fileSize) {
            $this->info("SFX file is ready: {$sfxLocal}");
            return true;
        }

        if (!$fileSize) {
            $this->error("Failed to query the SFX file size: {$sfxRemote}");
            return false;
        }



    }

    private function prepare(): AppBuild
    {
        File::ensureDirectoryExists(self::$outputDir);
        File::put(config_path('app_version'), self::$buildVersion);

        return $this;
    }

    private function clear(): void
    {
        self::$config = null;

        self::$box = null;
        File::delete(config_path('app_version'));
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

    public function __destruct()
    {
        if (self::$config !== null) {
            $this->clear();
        }
    }
}
