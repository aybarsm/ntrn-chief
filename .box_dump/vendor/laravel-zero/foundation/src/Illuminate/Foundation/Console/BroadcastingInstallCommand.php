<?php

namespace Illuminate\Foundation\Console;

use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\PhpExecutableFinder;

use function Laravel\Prompts\confirm;

#[AsCommand(name: 'install:broadcasting')]
class BroadcastingInstallCommand extends Command
{
use InteractsWithComposerPackages;






protected $signature = 'install:broadcasting
                    {--composer=global : Absolute path to the Composer binary which should be used to install packages}
                    {--force : Overwrite any existing broadcasting routes file}
                    {--without-reverb : Do not prompt to install Laravel Reverb}
                    {--without-node : Do not prompt to install Node dependencies}';






protected $description = 'Create a broadcasting channel routes file';






public function handle()
{
$this->call('config:publish', ['name' => 'broadcasting']);


if (! file_exists($broadcastingRoutesPath = $this->laravel->basePath('routes/channels.php')) || $this->option('force')) {
$this->components->info("Published 'channels' route file.");

copy(__DIR__.'/stubs/broadcasting-routes.stub', $broadcastingRoutesPath);
}

$this->uncommentChannelsRoutesFile();
$this->enableBroadcastServiceProvider();


if (! file_exists($echoScriptPath = $this->laravel->resourcePath('js/echo.js'))) {
if (! is_dir($directory = $this->laravel->resourcePath('js'))) {
mkdir($directory, 0755, true);
}

copy(__DIR__.'/stubs/echo-js.stub', $echoScriptPath);
}

if (file_exists($bootstrapScriptPath = $this->laravel->resourcePath('js/bootstrap.js'))) {
$bootstrapScript = file_get_contents(
$bootstrapScriptPath
);

if (! str_contains($bootstrapScript, './echo')) {
file_put_contents(
$bootstrapScriptPath,
trim($bootstrapScript.PHP_EOL.file_get_contents(__DIR__.'/stubs/echo-bootstrap-js.stub')).PHP_EOL,
);
}
}

$this->installReverb();

$this->installNodeDependencies();
}






protected function uncommentChannelsRoutesFile()
{
$appBootstrapPath = $this->laravel->bootstrapPath('app.php');

$content = file_get_contents($appBootstrapPath);

if (str_contains($content, '// channels: ')) {
(new Filesystem)->replaceInFile(
'// channels: ',
'channels: ',
$appBootstrapPath,
);
} elseif (str_contains($content, 'channels: ')) {
return;
} elseif (str_contains($content, 'commands: __DIR__.\'/../routes/console.php\',')) {
(new Filesystem)->replaceInFile(
'commands: __DIR__.\'/../routes/console.php\',',
'commands: __DIR__.\'/../routes/console.php\','.PHP_EOL.'        channels: __DIR__.\'/../routes/channels.php\',',
$appBootstrapPath,
);
}
}






protected function enableBroadcastServiceProvider()
{
$config = ($filesystem = new Filesystem)->get(app()->configPath('app.php'));

if (str_contains($config, '// App\Providers\BroadcastServiceProvider::class')) {
$filesystem->replaceInFile(
'// App\Providers\BroadcastServiceProvider::class',
'App\Providers\BroadcastServiceProvider::class',
app()->configPath('app.php'),
);
}
}






protected function installReverb()
{
if ($this->option('without-reverb') || InstalledVersions::isInstalled('laravel/reverb')) {
return;
}

$install = confirm('Would you like to install Laravel Reverb?', default: true);

if (! $install) {
return;
}

$this->requireComposerPackages($this->option('composer'), [
'laravel/reverb:@beta',
]);

$php = (new PhpExecutableFinder())->find(false) ?: 'php';

Process::run([
$php,
defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan',
'reverb:install',
]);

$this->components->info('Reverb installed successfully.');
}






protected function installNodeDependencies()
{
if ($this->option('without-node') || ! confirm('Would you like to install and build the Node dependencies required for broadcasting?', default: true)) {
return;
}

$this->components->info('Installing and building Node dependencies.');

if (file_exists(base_path('pnpm-lock.yaml'))) {
$commands = [
'pnpm add --save-dev laravel-echo pusher-js',
'pnpm run build',
];
} elseif (file_exists(base_path('yarn.lock'))) {
$commands = [
'yarn add --dev laravel-echo pusher-js',
'yarn run build',
];
} elseif (file_exists(base_path('bun.lockb'))) {
$commands = [
'bun add --dev laravel-echo pusher-js',
'bun run build',
];
} else {
$commands = [
'npm install --save-dev laravel-echo pusher-js',
'npm run build',
];
}

$command = Process::command(implode(' && ', $commands))
->path(base_path());

if (! windows_os()) {
$command->tty(true);
}

if ($command->run()->failed()) {
$this->components->warn("Node dependency installation failed. Please run the following commands manually: \n\n".implode(' && ', $commands));
} else {
$this->components->info('Node dependencies installed successfully.');
}
}
}
