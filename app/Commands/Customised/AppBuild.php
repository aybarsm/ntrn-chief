<?php

namespace App\Commands\Customised;

use Illuminate\Console\Application as Artisan;
use Illuminate\Support\Facades\File;
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
                            {name? : The build name}
                            {--build-version= : The build version, if not provided it will be asked}
                            {--timeout=300 : The timeout in seconds or 0 to disable}';

    protected $description = 'Build a single file executable (Customised)';

    private static ?string $config = null;

    private static ?string $box = null;

    private OutputInterface $originalOutput;

    public function handle(): void
    {
        $this->title('Building process');

        $this->build($this->input->getArgument('name') ?? $this->getBinary());
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
        if (! File::exists($this->app->buildsPath())) {
            File::makeDirectory($this->app->buildsPath());
        }

        $boxBinary = windows_os() ? '.\box.bat' : './box';

        $process = new Process(
            [$boxBinary, 'compile', '--working-dir='.base_path(), '--config='.base_path('box.json')] + $this->getExtraBoxOptions(),
            dirname(__DIR__, 2).'/bin',
            null,
            null,
            $this->getTimeout()
        );

        /** @phpstan-ignore-next-line This is an instance of `ConsoleOutputInterface` */
        $section = tap($this->originalOutput->section())->write('');

        $progressBar = new ProgressBar(
            $this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL ? new NullOutput : $section, 25
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

        $this->task('   2. <fg=yellow>Compile</> into a single file');

        $this->output->newLine();

        $pharPath = $this->app->basePath($this->getBinary()).'.phar';

        if (! File::exists($pharPath)) {
            throw new \RuntimeException('Failed to compile the application.');
        }

        File::move($pharPath, $this->app->buildsPath($name));

        return $this;
    }

    private function prepare(): AppBuild
    {
        $configFile = $this->app->configPath('app.php');
        self::$config = File::get($configFile);

        $config = include $configFile;

        $config['env'] = 'production';
        $version = $this->option('build-version') ?: text('Build version?', default: $config['version']);
        $config['version'] = $version;

        $boxFile = $this->app->basePath('box.json');
        self::$box = File::get($boxFile);

        $this->task(
            '   1. Moving application to <fg=yellow>production mode</>',
            function () use ($configFile, $config) {
                File::put($configFile, '<?php return '.var_export($config, true).';'.PHP_EOL);
            }
        );

        $boxContents = json_decode(self::$box, true);
        $boxContents['main'] = $this->getBinary();
        File::put($boxFile, json_encode($boxContents));

        File::put($configFile, '<?php return '.var_export($config, true).';'.PHP_EOL);

        return $this;
    }

    private function clear(): void
    {
        File::put($this->app->configPath('app.php'), self::$config);

        File::put($this->app->basePath('box.json'), self::$box);

        self::$config = null;

        self::$box = null;
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

    private function getExtraBoxOptions(): array
    {
        $extraBoxOptions = [];

        if ($this->output->isDebug()) {
            $extraBoxOptions[] = '--debug';
        }

        return $extraBoxOptions;
    }

    public function __destruct()
    {
        if (self::$config !== null) {
            $this->clear();
        }
    }
}
