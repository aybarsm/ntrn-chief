<?php

namespace Illuminate\Http\Middleware;

use Closure;
use Fruitcake\Cors\CorsService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

class HandleCors
{





protected $container;






protected $cors;








public function __construct(Container $container, CorsService $cors)
{
$this->container = $container;
$this->cors = $cors;
}








public function handle($request, Closure $next)
{
if (! $this->hasMatchingPath($request)) {
return $next($request);
}

$this->cors->setOptions($this->container['config']->get('cors', []));

if ($this->cors->isPreflightRequest($request)) {
$response = $this->cors->handlePreflightRequest($request);

$this->cors->varyHeader($response, 'Access-Control-Request-Method');

return $response;
}

$response = $next($request);

if ($request->getMethod() === 'OPTIONS') {
$this->cors->varyHeader($response, 'Access-Control-Request-Method');
}

return $this->cors->addActualRequestHeaders($response, $request);
}







protected function hasMatchingPath(Request $request): bool
{
$paths = $this->getPathsByHost($request->getHost());

foreach ($paths as $path) {
if ($path !== '/') {
$path = trim($path, '/');
}

if ($request->fullUrlIs($path) || $request->is($path)) {
return true;
}
}

return false;
}







protected function getPathsByHost(string $host)
{
$paths = $this->container['config']->get('cors.paths', []);

if (isset($paths[$host])) {
return $paths[$host];
}

return array_filter($paths, function ($path) {
return is_string($path);
});
}
}
