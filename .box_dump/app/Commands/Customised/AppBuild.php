<?php

namespace App\Commands\Customised;

use App\Enums\IndicatorType;
use App\Framework\Commands\TaskingCommand;
use App\Prompts\Progress;
use App\Services\Archive;
use App\Services\Helper;
use App\Traits\Configable;
use Illuminate\Console\Application as Artisan;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use function Illuminate\Filesystem\join_paths;

use App\Attributes\Console\CommandTask;

#[CommandTask('setParameters', IndicatorType::SPINNER, 'Set Build Parameters', true)]
#[CommandTask('compile', IndicatorType::SPINNER, 'Compile .phar file', true)]
#[CommandTask('checkDistributions', IndicatorType::SPINNER, 'Check distributions', false, true)]
#[CommandTask('downloadSfx', IndicatorType::PROGRESS, 'Download Micro Sfx')]
#[CommandTask('extractSfx', IndicatorType::SPINNER, 'Extract Micro Sfx')]
#[CommandTask('buildBinaries', IndicatorType::SPINNER, 'Build distribution binaries')]
class AppBuild extends TaskingCommand
{
use Configable;

protected $signature = 'app:build
    {--timeout=300 : The timeout in seconds or 0 to disable}
    {--b|box=* : Extra options to pass to Box}';

protected $description = 'Build a single file executable (Customised)';

public function handle (): void
{
$this->executeTasks();
}

protected function setParameters(): bool
{
$this->setSignalHandler('SIGINT', function () {
$this->cleanUp(true);
});

$this->config('set', 'ts.instance', Carbon::now('UTC'));
$tsSafe = $this->config('get', 'ts.instance')->format('Ymd\THis\Z');
$this->config('set', 'ts.safe', $tsSafe);

$this->config('set', 'name', Str::lower(config('app.name')));
$this->config('set', 'version', config('app.version'));

if ($this->config('get', 'version') === 'unreleased') {
$this->setTaskMessage('<error>App has not released yet.</error>');
return false;
}

$this->config('set', 'build', [
'initial' => $this->app->basePath($this->getBinary()).'.phar',
'id' => ($buildId = ($this->config('get', 'version') . '-' . $this->config('get', 'ts.safe'))),
'path' => ($buildPath = $this->app->buildsPath($buildId)),
'phar' => join_paths($buildPath, "{$this->config('get', 'name')}.phar"),
]);

$microPath = config('dev.build.micro.path');
$microUrl = Str::of(config('dev.build.micro.url'))->trim()->finish('/');

$distributions = [];
foreach (config('dev.build.micro.distributions', []) as $distribution => $micro) {
$micro['remote'] = Str::of($micro['remote'])->trim()->ltrim('/')->value();
$microLocal = join_paths($microPath, $micro['local']);
$microRemote = $microUrl->finish($micro['remote'])->value();
$archivePattern = config('dev.build.micro.archivePattern', '');
$remoteArchive = ! blank($archivePattern) && Str::isMatch($archivePattern, $microRemote);
$downloadSuffix = Str::of($micro['remote'])->start('downloads/')->split('/\//', -1, PREG_SPLIT_NO_EMPTY)->toArray();
$downloadArchivePath = join_paths(dirname($microLocal), ...$downloadSuffix);
$downloadPath = $remoteArchive ? $downloadArchivePath : $microLocal;

$distributions[$distribution] = [
'target' => $distribution,
'output' => join_paths($this->config('get', 'build.path'), "{$this->config('get', 'name')}-{$distribution}"),
'sfx' => [
'local' => $microLocal,
'localExists' => File::exists($microLocal),
'remote' => $microRemote,
'downloadPath' => $downloadPath,
'downloadExists' => File::exists($downloadPath),
'remoteArchive' => $remoteArchive,
'archiveFile' => $micro['archiveFile'],
'extractDir' => join_paths(config('dev.temp'), Str::uuid()),
],
];
}

$this->config('set', "build.distributions", $distributions);

$this->config('set', 'box', [
'binary' => join_paths(base_path(), 'vendor', 'laravel-zero', 'framework', 'bin', (windows_os() ? 'box.bat' : 'box')),
]);

return true;
}

protected function compile(): bool
{
$initial = $this->config('get', 'build.initial');
$phar = $this->config('get', 'build.phar');
$boxDump = join_paths(dirname($initial), '.box_dump');

if (File::exists($boxDump) && File::isDirectory($boxDump)){
File::deleteDirectory($boxDump);
$this->setTaskMessage("<comment>Old .box_dump deleted.</comment>");
}

if (File::exists($initial)){
File::delete($initial);
$this->setTaskMessage("<comment>Old initial .phar file deleted.</comment>");
}

File::put(config('dev.build.app_version'), $this->config('get', 'version'));

$process = Process::timeout($this->getTimeout())
->command([$this->config('get', 'box.binary'), 'compile'] + $this->getBoxOptions())
->run();

$initFile = File::exists($initial);

if ($process->successful()){
if ($initFile) {
File::ensureDirectoryExists(dirname($phar));
File::move($initial, $phar);
$this->setTaskMessage("<info>Compile was successful and output is at {$phar}</info>");
}else {
$this->setTaskMessage("<comment>Compile was successful but initial output does not exist at {$initial}</comment>");
}
}else {
$this->setTaskMessage("<error>Failed to compile the application. Exit Code: {$process->exitCode()}</error>");
}

return $process->successful() && $initFile;
}

protected function checkDistributions(): bool|null
{
$distributions = $this->config('get', 'build.distributions', []);
if (count($distributions) == 0){
$this->setTaskMessage("<comment>No distributions to check.</comment>");
return null;
}

$this->taskMessageTitle = ' | Distributions to be built:';
foreach(array_keys($distributions) as $key => $distribution){
$order = $key + 1;
$this->setTaskMessage("<info>#{$order}: {$distribution}</info>");
}

return true;
}

protected function downloadSfx(): bool
{
$distributions = $this->config('get', 'build.distributions', []);

foreach($distributions as $distribution){
$sfx = $distribution['sfx'];
if ($sfx['localExists']){
$this->setTaskMessage("<comment>{$distribution['target']} Sfx already exists locally at {$sfx['local']}</comment>");
continue;
}elseif ($sfx['downloadExists']){
$this->setTaskMessage("<comment>{$distribution['target']} Sfx already downloaded at {$sfx['downloadPath']}</comment>");
continue;
}

File::ensureDirectoryExists(dirname($sfx['downloadPath']));
$this->indicator = new Progress(steps: 0);
$this->indicator = Helper::downloadProgress($this->indicator, $sfx['remote'], "for {$distribution['target']}");
$this->indicator->config('set', 'auto.finish', true);
$this->indicator->config('set', 'auto.clear', true);
$this->indicator->config('set', 'show.finish', 2);

$response = Http::sink($sfx['downloadPath'])->withOptions([
'progress' => function ($dlSize, $dlCompleted) {
$this->indicator->progress($dlCompleted);
},
'on_headers' => function (ResponseInterface $response) use($sfx) {
$this->indicator->total((int)$response->getHeaderLine('Content-Length'));
}
])->get($sfx['remote']);

if ($response->successful()) {
$this->setTaskMessage("<info>{$distribution['target']} Sfx downloaded successfully to {$sfx['downloadPath']}</info>");
}else {
$this->setTaskMessage("<error>{$distribution['target']} Sfx download failed.</error>");
}
}

return true;
}
protected function extractSfx(): bool
{
$distributions = $this->config('get', 'build.distributions', []);

foreach($distributions as $distribution){
$sfx = $distribution['sfx'];

if ($sfx['localExists'] || ! $sfx['remoteArchive']){
continue;
}

if (! File::exists($sfx['downloadPath'])){
$this->setTaskMessage("<error>{$distribution['target']} Sfx archive does not exist at {$sfx['downloadPath']}</error>");
continue;
}

File::ensureDirectoryExists($sfx['extractDir']);

$result = false;
try{
Archive::extractTo($sfx['downloadPath'], $sfx['extractDir']);
$result = true;
}catch (\Throwable $exception){
$this->setTaskMessage("<error>{$distribution['target']} Sfx archive could not be extracted from {$sfx['downloadPath']}</error>");
$this->setTaskMessage("<error>Exception Message: {$exception->getMessage()}</error>");
}

if (! $result){
continue;
}

$extractedSfx = join_paths($sfx['extractDir'], $sfx['archiveFile']);

if (! File::exists($extractedSfx)){
$this->setTaskMessage("<error>{$distribution['target']} Sfx archive could not be extracted from {$extractedSfx}</error>");
}else {
File::ensureDirectoryExists(dirname($sfx['local']));
File::move($extractedSfx, $sfx['local']);
$this->config('set', "build.distributions.{$distribution['target']}.sfx.localExists", true);
$this->setTaskMessage("<info>{$distribution['target']} Sfx archive extracted successfully to {$sfx['local']}</info>");
}
}

return true;
}

protected function buildBinaries(): bool
{
$distributions = $this->config('get', 'build.distributions');
$phar = $this->config('get', 'build.phar');

foreach($distributions as $distribution) {
$sfx = $distribution['sfx'];

if (! $sfx['localExists']){
$this->setTaskMessage("<error>{$distribution['target']} Sfx does not exist at {$sfx['local']}</error>");
continue;
}

$output = $distribution['output'];
File::ensureDirectoryExists(dirname($output));

$process = Process::timeout($this->getTimeout())->command("cat {$sfx['local']}  {$phar} > {$output}")->run();
if ($process->successful()){
$this->setTaskMessage("<info>{$distribution['target']} built successfully at {$output}</info>");
if (! blank($chmod = config('dev.build.chmod')) && is_string($chmod) && is_numeric($chmod) && strlen($chmod) === 4) {
File::chmod($output, octdec(config('dev.build.chmod')));
}
}else {
$this->setTaskMessage("<error>{$distribution['target']} build failed. Exit Code: {$process->exitCode()}</error>");
if (File::exists($output)){
File::delete($output);
}
}
}

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
private function cleanUp(bool $isSignal = false): AppBuild|int
{
if (File::exists(config('dev.build.app_version'))) {
File::delete(config('dev.build.app_version'));
}

foreach($this->config('get', 'build.distributions', []) as $distribution){
if (File::exists($distribution['sfx']['extractDir'])){
File::deleteDirectory($distribution['sfx']['extractDir']);
}
}

return $isSignal ? self::SUCCESS : $this;
}

public function __destruct()
{
$this->cleanUp();
}
}
