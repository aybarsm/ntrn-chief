<?php

declare(strict_types=1);










namespace LaravelZero\Framework\Components\View;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Components\AbstractInstaller;




final class Installer extends AbstractInstaller
{



protected $name = 'install:view';




protected $description = 'View: Blade View Components';




private const CONFIG_FILE = __DIR__.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'view.php';




public function install(): void
{
$this->require('illuminate/view "^11.5"');

$this->task(
'Creating resources/views folder',
function () {
if (! File::exists(base_path('resources/views'))) {
File::makeDirectory(base_path('resources/views'), 0755, true, true);

return true;
}

return false;
}
);

$this->task(
'Creating default view configuration',
function () {
if (! File::exists(config_path('view.php'))) {
return File::copy(
static::CONFIG_FILE,
$this->app->configPath('view.php')
);
}

return false;
}
);

$this->task(
'Creating cache storage folder',
function () {
if (File::exists(base_path('storage/app/.gitignore')) &&
File::exists(base_path('storage/framework/views/.gitignore'))
) {
return false;
}

if (! File::exists(base_path('storage/app'))) {
File::makeDirectory(base_path('storage/app'), 0755, true, true);
}

if (! File::exists(base_path('storage/app/.gitignore'))) {
File::append(base_path('storage/app/.gitignore'), "*\n!.gitignore");
}

if (! File::exists(base_path('storage/framework/views'))) {
File::makeDirectory(base_path('storage/framework/views'), 0755, true, true);
}

if (! File::exists(base_path('storage/framework/views/.gitignore'))) {
File::append(base_path('storage/framework/views/.gitignore'), "*\n!.gitignore");
}

return true;
}
);
}
}
