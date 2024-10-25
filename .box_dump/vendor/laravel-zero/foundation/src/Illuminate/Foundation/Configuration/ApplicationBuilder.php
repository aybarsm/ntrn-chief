<?php

namespace Illuminate\Foundation\Configuration;

use Closure;
use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as AppEventServiceProvider;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as AppRouteServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Laravel\Folio\Folio;

class ApplicationBuilder
{





protected array $pendingProviders = [];






protected array $pageMiddleware = [];




public function __construct(protected Application $app)
{
}






public function withKernels()
{
$this->app->singleton(
\Illuminate\Contracts\Http\Kernel::class,
\Illuminate\Foundation\Http\Kernel::class,
);

$this->app->singleton(
\Illuminate\Contracts\Console\Kernel::class,
\Illuminate\Foundation\Console\Kernel::class,
);

return $this;
}








public function withProviders(array $providers = [], bool $withBootstrapProviders = true)
{
RegisterProviders::merge(
$providers,
$withBootstrapProviders
? $this->app->getBootstrapProvidersPath()
: null
);

return $this;
}







public function withEvents(array|bool $discover = [])
{
if (is_array($discover) && count($discover) > 0) {
AppEventServiceProvider::setEventDiscoveryPaths($discover);
}

if ($discover === false) {
AppEventServiceProvider::disableEventDiscovery();
}

if (! isset($this->pendingProviders[AppEventServiceProvider::class])) {
$this->app->booting(function () {
$this->app->register(AppEventServiceProvider::class);
});
}

$this->pendingProviders[AppEventServiceProvider::class] = true;

return $this;
}








public function withBroadcasting(string $channels, array $attributes = [])
{
$this->app->booted(function () use ($channels, $attributes) {
Broadcast::routes(! empty($attributes) ? $attributes : null);

if (file_exists($channels)) {
require $channels;
}
});

return $this;
}














public function withRouting(?Closure $using = null,
?string $web = null,
?string $api = null,
?string $commands = null,
?string $channels = null,
?string $pages = null,
?string $health = null,
string $apiPrefix = 'api',
?callable $then = null)
{
if (is_null($using) && (is_string($web) || is_string($api) || is_string($pages) || is_string($health)) || is_callable($then)) {
$using = $this->buildRoutingCallback($web, $api, $pages, $health, $apiPrefix, $then);
}

AppRouteServiceProvider::loadRoutesUsing($using);

$this->app->booting(function () {
$this->app->register(AppRouteServiceProvider::class, force: true);
});

if (is_string($commands) && realpath($commands) !== false) {
$this->withCommands([$commands]);
}

if (is_string($channels) && realpath($channels) !== false) {
$this->withBroadcasting($channels);
}

return $this;
}












protected function buildRoutingCallback(?string $web,
?string $api,
?string $pages,
?string $health,
string $apiPrefix,
?callable $then)
{
return function () use ($web, $api, $pages, $health, $apiPrefix, $then) {
if (is_string($api) && realpath($api) !== false) {
Route::middleware('api')->prefix($apiPrefix)->group($api);
}

if (is_string($health)) {
Route::middleware('web')->get($health, function () {
Event::dispatch(new DiagnosingHealth);

return View::file(__DIR__.'/../resources/health-up.blade.php');
});
}

if (is_string($web) && realpath($web) !== false) {
Route::middleware('web')->group($web);
}

if (is_string($pages) &&
realpath($pages) !== false &&
class_exists(Folio::class)) {
Folio::route($pages, middleware: $this->pageMiddleware);
}

if (is_callable($then)) {
$then($this->app);
}
};
}







public function withMiddleware(?callable $callback = null)
{
$this->app->afterResolving(HttpKernel::class, function ($kernel) use ($callback) {
$middleware = (new Middleware)
->redirectGuestsTo(fn () => route('login'));

if (! is_null($callback)) {
$callback($middleware);
}

$this->pageMiddleware = $middleware->getPageMiddleware();
$kernel->setGlobalMiddleware($middleware->getGlobalMiddleware());
$kernel->setMiddlewareGroups($middleware->getMiddlewareGroups());
$kernel->setMiddlewareAliases($middleware->getMiddlewareAliases());

if ($priorities = $middleware->getMiddlewarePriority()) {
$kernel->setMiddlewarePriority($priorities);
}
});

return $this;
}







public function withCommands(array $commands = [])
{
if (empty($commands)) {
$commands = [$this->app->path('Console/Commands')];
}

$this->app->afterResolving(ConsoleKernel::class, function ($kernel) use ($commands) {
[$commands, $paths] = collect($commands)->partition(fn ($command) => class_exists($command));
[$routes, $paths] = $paths->partition(fn ($path) => is_file($path));

$this->app->booted(static function () use ($kernel, $commands, $paths, $routes) {
$kernel->addCommands($commands->all());
$kernel->addCommandPaths($paths->all());
$kernel->addCommandRoutePaths($routes->all());
});
});

return $this;
}







protected function withCommandRouting(array $paths)
{
$this->app->afterResolving(ConsoleKernel::class, function ($kernel) use ($paths) {
$this->app->booted(fn () => $kernel->addCommandRoutePaths($paths));
});
}







public function withSchedule(callable $callback)
{
Artisan::starting(fn () => $callback($this->app->make(Schedule::class)));

return $this;
}







public function withExceptions(?callable $using = null)
{
$this->app->singleton(
\Illuminate\Contracts\Debug\ExceptionHandler::class,
\Illuminate\Foundation\Exceptions\Handler::class
);

$using ??= fn () => true;

$this->app->afterResolving(
\Illuminate\Foundation\Exceptions\Handler::class,
fn ($handler) => $using(new Exceptions($handler)),
);

return $this;
}







public function withBindings(array $bindings)
{
return $this->registered(function ($app) use ($bindings) {
foreach ($bindings as $abstract => $concrete) {
$app->bind($abstract, $concrete);
}
});
}







public function withSingletons(array $singletons)
{
return $this->registered(function ($app) use ($singletons) {
foreach ($singletons as $abstract => $concrete) {
if (is_string($abstract)) {
$app->singleton($abstract, $concrete);
} else {
$app->singleton($concrete);
}
}
});
}







public function registered(callable $callback)
{
$this->app->registered($callback);

return $this;
}







public function booting(callable $callback)
{
$this->app->booting($callback);

return $this;
}







public function booted(callable $callback)
{
$this->app->booted($callback);

return $this;
}






public function create()
{
return $this->app;
}
}
