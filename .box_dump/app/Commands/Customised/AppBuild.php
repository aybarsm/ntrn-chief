<?php

namespace App\Commands\Customised;

use Illuminate\Console\Application as Artisan;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
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

private static ?string $config = null;

private static ?string $box = null;

private OutputInterface $originalOutput;

public function handle(): void
{
self::$buildTimestamp = Carbon::now('UTC');
self::$outputDir = base_path('dev' . DIRECTORY_SEPARATOR . 'builds');
self::$buildVersion = config('app.version');

if (self::$buildVersion === 'unreleased'){
$this->error('App has not released yet.');
return;
}

self::$buildName = 'ntrn-' . self::$buildVersion . '-' . self::$buildTimestamp->format('Ymd\THis\Z');

$this->title('Building process');

$this->build(self::$buildName);
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

$process = new Process(
command: [$boxBinary, 'compile'] + $this->getBoxOptions(),
env: null,
input: null,
timeout: $this->getTimeout()
);

/**
@phpstan-ignore-next-line */
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

File::move($pharPath, $output);

return $this;
}

private function prepare(): AppBuild
{
File::ensureDirectoryExists(self::$outputDir);

return $this;
}

private function clear(): void
{
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
