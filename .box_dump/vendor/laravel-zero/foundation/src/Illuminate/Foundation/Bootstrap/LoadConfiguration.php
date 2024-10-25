<?php

namespace Illuminate\Foundation\Bootstrap;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Foundation\Application;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class LoadConfiguration
{






public function bootstrap(Application $app)
{
$items = [];




if (file_exists($cached = $app->getCachedConfigPath())) {
$items = require $cached;

$app->instance('config_loaded_from_cache', $loadedFromCache = true);
}




$app->instance('config', $config = new Repository($items));

if (! isset($loadedFromCache)) {
$this->loadConfigurationFiles($app, $config);
}




$app->detectEnvironment(fn () => $config->get('app.env', 'production'));

date_default_timezone_set($config->get('app.timezone', 'UTC'));

mb_internal_encoding('UTF-8');
}










protected function loadConfigurationFiles(Application $app, RepositoryContract $repository)
{
$files = $this->getConfigurationFiles($app);





$base = $this->getBaseConfiguration();

foreach ($files as $name => $path) {
$base = $this->loadConfigurationFile($repository, $name, $path, $base);
}

foreach ($base as $name => $config) {
$repository->set($name, $config);
}
}










protected function loadConfigurationFile(RepositoryContract $repository, $name, $path, array $base)
{
$config = require $path;

if (isset($base[$name])) {
$config = array_merge($base[$name], $config);

foreach ($this->mergeableOptions($name) as $option) {
if (isset($config[$option])) {
$config[$option] = array_merge($base[$name][$option], $config[$option]);
}
}

unset($base[$name]);
}

$repository->set($name, $config);

return $base;
}







protected function mergeableOptions($name)
{
return [
'auth' => ['guards', 'providers', 'passwords'],
'broadcasting' => ['connections'],
'cache' => ['stores'],
'database' => ['connections'],
'filesystems' => ['disks'],
'logging' => ['channels'],
'mail' => ['mailers'],
'queue' => ['connections'],
][$name] ?? [];
}







protected function getConfigurationFiles(Application $app)
{
$files = [];

$configPath = realpath($app->configPath());

if (! $configPath) {
return [];
}

foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
$directory = $this->getNestedDirectory($file, $configPath);

$files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
}

ksort($files, SORT_NATURAL);

return $files;
}








protected function getNestedDirectory(SplFileInfo $file, $configPath)
{
$directory = $file->getPath();

if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
$nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
}

return $nested;
}






protected function getBaseConfiguration()
{
$config = [];

foreach (Finder::create()->files()->name('*.php')->in(__DIR__.'/../../../../config') as $file) {
$config[basename($file->getRealPath(), '.php')] = require $file->getRealPath();
}

return $config;
}
}
