<?php

namespace Illuminate\Foundation\Console;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Routing\ViewController;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionFunction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Terminal;

#[AsCommand(name: 'route:list')]
class RouteListCommand extends Command
{





protected $name = 'route:list';






protected $description = 'List all registered routes';






protected $router;






protected $headers = ['Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware'];






protected static $terminalWidthResolver;






protected $verbColors = [
'ANY' => 'red',
'GET' => 'blue',
'HEAD' => '#6C7280',
'OPTIONS' => '#6C7280',
'POST' => 'yellow',
'PUT' => 'yellow',
'PATCH' => 'yellow',
'DELETE' => 'red',
];







public function __construct(Router $router)
{
parent::__construct();

$this->router = $router;
}






public function handle()
{
if (! $this->output->isVeryVerbose()) {
$this->router->flushMiddlewareGroups();
}

if (! $this->router->getRoutes()->count()) {
return $this->components->error("Your application doesn't have any routes.");
}

if (empty($routes = $this->getRoutes())) {
return $this->components->error("Your application doesn't have any routes matching the given criteria.");
}

$this->displayRoutes($routes);
}






protected function getRoutes()
{
$routes = collect($this->router->getRoutes())->map(function ($route) {
return $this->getRouteInformation($route);
})->filter()->all();

if (($sort = $this->option('sort')) !== null) {
$routes = $this->sortRoutes($sort, $routes);
} else {
$routes = $this->sortRoutes('uri', $routes);
}

if ($this->option('reverse')) {
$routes = array_reverse($routes);
}

return $this->pluckColumns($routes);
}







protected function getRouteInformation(Route $route)
{
return $this->filterRoute([
'domain' => $route->domain(),
'method' => implode('|', $route->methods()),
'uri' => $route->uri(),
'name' => $route->getName(),
'action' => ltrim($route->getActionName(), '\\'),
'middleware' => $this->getMiddleware($route),
'vendor' => $this->isVendorRoute($route),
]);
}








protected function sortRoutes($sort, array $routes)
{
if (Str::contains($sort, ',')) {
$sort = explode(',', $sort);
}

return collect($routes)
->sortBy($sort)
->toArray();
}







protected function pluckColumns(array $routes)
{
return array_map(function ($route) {
return Arr::only($route, $this->getColumns());
}, $routes);
}







protected function displayRoutes(array $routes)
{
$routes = collect($routes);

$this->output->writeln(
$this->option('json') ? $this->asJson($routes) : $this->forCli($routes)
);
}







protected function getMiddleware($route)
{
return collect($this->router->gatherRouteMiddleware($route))->map(function ($middleware) {
return $middleware instanceof Closure ? 'Closure' : $middleware;
})->implode("\n");
}







protected function isVendorRoute(Route $route)
{
if ($route->action['uses'] instanceof Closure) {
$path = (new ReflectionFunction($route->action['uses']))
->getFileName();
} elseif (is_string($route->action['uses']) &&
str_contains($route->action['uses'], 'SerializableClosure')) {
return false;
} elseif (is_string($route->action['uses'])) {
if ($this->isFrameworkController($route)) {
return false;
}

$path = (new ReflectionClass($route->getControllerClass()))
->getFileName();
} else {
return false;
}

return str_starts_with($path, base_path('vendor'));
}







protected function isFrameworkController(Route $route)
{
return in_array($route->getControllerClass(), [
'\Illuminate\Routing\RedirectController',
'\Illuminate\Routing\ViewController',
], true);
}







protected function filterRoute(array $route)
{
if (($this->option('name') && ! Str::contains((string) $route['name'], $this->option('name'))) ||
($this->option('path') && ! Str::contains($route['uri'], $this->option('path'))) ||
($this->option('method') && ! Str::contains($route['method'], strtoupper($this->option('method')))) ||
($this->option('domain') && ! Str::contains((string) $route['domain'], $this->option('domain'))) ||
($this->option('except-vendor') && $route['vendor']) ||
($this->option('only-vendor') && ! $route['vendor'])) {
return;
}

if ($this->option('except-path')) {
foreach (explode(',', $this->option('except-path')) as $path) {
if (str_contains($route['uri'], $path)) {
return;
}
}
}

return $route;
}






protected function getHeaders()
{
return Arr::only($this->headers, array_keys($this->getColumns()));
}






protected function getColumns()
{
return array_map('strtolower', $this->headers);
}







protected function parseColumns(array $columns)
{
$results = [];

foreach ($columns as $column) {
if (str_contains($column, ',')) {
$results = array_merge($results, explode(',', $column));
} else {
$results[] = $column;
}
}

return array_map('strtolower', $results);
}







protected function asJson($routes)
{
return $routes
->map(function ($route) {
$route['middleware'] = empty($route['middleware']) ? [] : explode("\n", $route['middleware']);

return $route;
})
->values()
->toJson();
}







protected function forCli($routes)
{
$routes = $routes->map(
fn ($route) => array_merge($route, [
'action' => $this->formatActionForCli($route),
'method' => $route['method'] == 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS' ? 'ANY' : $route['method'],
'uri' => $route['domain'] ? ($route['domain'].'/'.ltrim($route['uri'], '/')) : $route['uri'],
]),
);

$maxMethod = mb_strlen($routes->max('method'));

$terminalWidth = $this->getTerminalWidth();

$routeCount = $this->determineRouteCountOutput($routes, $terminalWidth);

return $routes->map(function ($route) use ($maxMethod, $terminalWidth) {
[
'action' => $action,
'domain' => $domain,
'method' => $method,
'middleware' => $middleware,
'uri' => $uri,
] = $route;

$middleware = Str::of($middleware)->explode("\n")->filter()->whenNotEmpty(
fn ($collection) => $collection->map(
fn ($middleware) => sprintf('         %s⇂ %s', str_repeat(' ', $maxMethod), $middleware)
)
)->implode("\n");

$spaces = str_repeat(' ', max($maxMethod + 6 - mb_strlen($method), 0));

$dots = str_repeat('.', max(
$terminalWidth - mb_strlen($method.$spaces.$uri.$action) - 6 - ($action ? 1 : 0), 0
));

$dots = empty($dots) ? $dots : " $dots";

if ($action && ! $this->output->isVerbose() && mb_strlen($method.$spaces.$uri.$action.$dots) > ($terminalWidth - 6)) {
$action = substr($action, 0, $terminalWidth - 7 - mb_strlen($method.$spaces.$uri.$dots)).'…';
}

$method = Str::of($method)->explode('|')->map(
fn ($method) => sprintf('<fg=%s>%s</>', $this->verbColors[$method] ?? 'default', $method),
)->implode('<fg=#6C7280>|</>');

return [sprintf(
'  <fg=white;options=bold>%s</> %s<fg=white>%s</><fg=#6C7280>%s %s</>',
$method,
$spaces,
preg_replace('#({[^}]+})#', '<fg=yellow>$1</>', $uri),
$dots,
str_replace('   ', ' › ', $action ?? ''),
), $this->output->isVerbose() && ! empty($middleware) ? "<fg=#6C7280>$middleware</>" : null];
})
->flatten()
->filter()
->prepend('')
->push('')->push($routeCount)->push('')
->toArray();
}







protected function formatActionForCli($route)
{
['action' => $action, 'name' => $name] = $route;

if ($action === 'Closure' || $action === ViewController::class) {
return $name;
}

$name = $name ? "$name   " : null;

$rootControllerNamespace = $this->laravel[UrlGenerator::class]->getRootControllerNamespace()
?? ($this->laravel->getNamespace().'Http\\Controllers');

if (str_starts_with($action, $rootControllerNamespace)) {
return $name.substr($action, mb_strlen($rootControllerNamespace) + 1);
}

$actionClass = explode('@', $action)[0];

if (class_exists($actionClass) && str_starts_with((new ReflectionClass($actionClass))->getFilename(), base_path('vendor'))) {
$actionCollection = collect(explode('\\', $action));

return $name.$actionCollection->take(2)->implode('\\').'   '.$actionCollection->last();
}

return $name.$action;
}








protected function determineRouteCountOutput($routes, $terminalWidth)
{
$routeCountText = 'Showing ['.$routes->count().'] routes';

$offset = $terminalWidth - mb_strlen($routeCountText) - 2;

$spaces = str_repeat(' ', $offset);

return $spaces.'<fg=blue;options=bold>Showing ['.$routes->count().'] routes</>';
}






public static function getTerminalWidth()
{
return is_null(static::$terminalWidthResolver)
? (new Terminal)->getWidth()
: call_user_func(static::$terminalWidthResolver);
}







public static function resolveTerminalWidthUsing($resolver)
{
static::$terminalWidthResolver = $resolver;
}






protected function getOptions()
{
return [
['json', null, InputOption::VALUE_NONE, 'Output the route list as JSON'],
['method', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by method'],
['name', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by name'],
['domain', null, InputOption::VALUE_OPTIONAL, 'Filter the routes by domain'],
['path', null, InputOption::VALUE_OPTIONAL, 'Only show routes matching the given path pattern'],
['except-path', null, InputOption::VALUE_OPTIONAL, 'Do not display the routes matching the given path pattern'],
['reverse', 'r', InputOption::VALUE_NONE, 'Reverse the ordering of the routes'],
['sort', null, InputOption::VALUE_OPTIONAL, 'The column (domain, method, uri, name, action, middleware) to sort by', 'uri'],
['except-vendor', null, InputOption::VALUE_NONE, 'Do not display routes defined by vendor packages'],
['only-vendor', null, InputOption::VALUE_NONE, 'Only display routes defined by vendor packages'],
];
}
}