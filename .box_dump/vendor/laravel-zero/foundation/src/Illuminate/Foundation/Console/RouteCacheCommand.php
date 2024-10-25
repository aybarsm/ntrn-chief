<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\RouteCollection;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'route:cache')]
class RouteCacheCommand extends Command
{





protected $name = 'route:cache';






protected $description = 'Create a route cache file for faster route registration';






protected $files;







public function __construct(Filesystem $files)
{
parent::__construct();

$this->files = $files;
}






public function handle()
{
$this->callSilent('route:clear');

$routes = $this->getFreshApplicationRoutes();

if (count($routes) === 0) {
return $this->components->error("Your application doesn't have any routes.");
}

foreach ($routes as $route) {
$route->prepareForSerialization();
}

$this->files->put(
$this->laravel->getCachedRoutesPath(), $this->buildRouteCacheFile($routes)
);

$this->components->info('Routes cached successfully.');
}






protected function getFreshApplicationRoutes()
{
return tap($this->getFreshApplication()['router']->getRoutes(), function ($routes) {
$routes->refreshNameLookups();
$routes->refreshActionLookups();
});
}






protected function getFreshApplication()
{
return tap(require $this->laravel->bootstrapPath('app.php'), function ($app) {
$app->make(ConsoleKernelContract::class)->bootstrap();
});
}







protected function buildRouteCacheFile(RouteCollection $routes)
{
$stub = $this->files->get(__DIR__.'/stubs/routes.stub');

return str_replace('{{routes}}', var_export($routes->compile(), true), $stub);
}
}
