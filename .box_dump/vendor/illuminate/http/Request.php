<?php

namespace Illuminate\Http;

use ArrayAccess;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Session\SymfonySessionDecorator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Session\SessionInterface;






class Request extends SymfonyRequest implements Arrayable, ArrayAccess
{
use Concerns\CanBePrecognitive,
Concerns\InteractsWithContentTypes,
Concerns\InteractsWithFlashData,
Concerns\InteractsWithInput,
Macroable;






protected $json;






protected $convertedFiles;






protected $userResolver;






protected $routeResolver;






public static function capture()
{
static::enableHttpMethodParameterOverride();

return static::createFromBase(SymfonyRequest::createFromGlobals());
}






public function instance()
{
return $this;
}






public function method()
{
return $this->getMethod();
}






public function root()
{
return rtrim($this->getSchemeAndHttpHost().$this->getBaseUrl(), '/');
}






public function url()
{
return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
}






public function fullUrl()
{
$query = $this->getQueryString();

$question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

return $query ? $this->url().$question.$query : $this->url();
}







public function fullUrlWithQuery(array $query)
{
$question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

return count($this->query()) > 0
? $this->url().$question.Arr::query(array_merge($this->query(), $query))
: $this->fullUrl().$question.Arr::query($query);
}







public function fullUrlWithoutQuery($keys)
{
$query = Arr::except($this->query(), $keys);

$question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

return count($query) > 0
? $this->url().$question.Arr::query($query)
: $this->url();
}






public function path()
{
$pattern = trim($this->getPathInfo(), '/');

return $pattern === '' ? '/' : $pattern;
}






public function decodedPath()
{
return rawurldecode($this->path());
}








public function segment($index, $default = null)
{
return Arr::get($this->segments(), $index - 1, $default);
}






public function segments()
{
$segments = explode('/', $this->decodedPath());

return array_values(array_filter($segments, function ($value) {
return $value !== '';
}));
}







public function is(...$patterns)
{
$path = $this->decodedPath();

return collect($patterns)->contains(fn ($pattern) => Str::is($pattern, $path));
}







public function routeIs(...$patterns)
{
return $this->route() && $this->route()->named(...$patterns);
}







public function fullUrlIs(...$patterns)
{
$url = $this->fullUrl();

return collect($patterns)->contains(fn ($pattern) => Str::is($pattern, $url));
}






public function host()
{
return $this->getHost();
}






public function httpHost()
{
return $this->getHttpHost();
}






public function schemeAndHttpHost()
{
return $this->getSchemeAndHttpHost();
}






public function ajax()
{
return $this->isXmlHttpRequest();
}






public function pjax()
{
return $this->headers->get('X-PJAX') == true;
}






public function prefetch()
{
return strcasecmp($this->server->get('HTTP_X_MOZ') ?? '', 'prefetch') === 0 ||
strcasecmp($this->headers->get('Purpose') ?? '', 'prefetch') === 0 ||
strcasecmp($this->headers->get('Sec-Purpose') ?? '', 'prefetch') === 0;
}






public function secure()
{
return $this->isSecure();
}






public function ip()
{
return $this->getClientIp();
}






public function ips()
{
return $this->getClientIps();
}






public function userAgent()
{
return $this->headers->get('User-Agent');
}







public function merge(array $input)
{
$this->getInputSource()->add($input);

return $this;
}







public function mergeIfMissing(array $input)
{
return $this->merge(collect($input)->filter(function ($value, $key) {
return $this->missing($key);
})->toArray());
}







public function replace(array $input)
{
$this->getInputSource()->replace($input);

return $this;
}










#[\Override]
public function get(string $key, mixed $default = null): mixed
{
return parent::get($key, $default);
}








public function json($key = null, $default = null)
{
if (! isset($this->json)) {
$this->json = new InputBag((array) json_decode($this->getContent() ?: '[]', true));
}

if (is_null($key)) {
return $this->json;
}

return data_get($this->json->all(), $key, $default);
}






protected function getInputSource()
{
if ($this->isJson()) {
return $this->json();
}

return in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
}








public static function createFrom(self $from, $to = null)
{
$request = $to ?: new static;

$files = array_filter($from->files->all());

$request->initialize(
$from->query->all(),
$from->request->all(),
$from->attributes->all(),
$from->cookies->all(),
$files,
$from->server->all(),
$from->getContent()
);

$request->headers->replace($from->headers->all());

$request->setRequestLocale($from->getLocale());

$request->setDefaultRequestLocale($from->getDefaultLocale());

$request->setJson($from->json());

if ($from->hasSession() && $session = $from->session()) {
$request->setLaravelSession($session);
}

$request->setUserResolver($from->getUserResolver());

$request->setRouteResolver($from->getRouteResolver());

return $request;
}







public static function createFromBase(SymfonyRequest $request)
{
$newRequest = new static(
$request->query->all(), $request->request->all(), $request->attributes->all(),
$request->cookies->all(), (new static)->filterFiles($request->files->all()) ?? [], $request->server->all()
);

$newRequest->headers->replace($request->headers->all());

$newRequest->content = $request->content;

if ($newRequest->isJson()) {
$newRequest->request = $newRequest->json();
}

return $newRequest;
}






#[\Override]
public function duplicate(?array $query = null, ?array $request = null, ?array $attributes = null, ?array $cookies = null, ?array $files = null, ?array $server = null): static
{
return parent::duplicate($query, $request, $attributes, $cookies, $this->filterFiles($files), $server);
}







protected function filterFiles($files)
{
if (! $files) {
return;
}

foreach ($files as $key => $file) {
if (is_array($file)) {
$files[$key] = $this->filterFiles($files[$key]);
}

if (empty($files[$key])) {
unset($files[$key]);
}
}

return $files;
}




#[\Override]
public function hasSession(bool $skipIfUninitialized = false): bool
{
return $this->session instanceof SymfonySessionDecorator;
}




#[\Override]
public function getSession(): SessionInterface
{
return $this->hasSession()
? $this->session
: throw new SessionNotFoundException;
}








public function session()
{
if (! $this->hasSession()) {
throw new RuntimeException('Session store not set on request.');
}

return $this->session->store;
}







public function setLaravelSession($session)
{
$this->session = new SymfonySessionDecorator($session);
}







public function setRequestLocale(string $locale)
{
$this->locale = $locale;
}







public function setDefaultRequestLocale(string $locale)
{
$this->defaultLocale = $locale;
}







public function user($guard = null)
{
return call_user_func($this->getUserResolver(), $guard);
}








public function route($param = null, $default = null)
{
$route = call_user_func($this->getRouteResolver());

if (is_null($route) || is_null($param)) {
return $route;
}

return $route->parameter($param, $default);
}








public function fingerprint()
{
if (! $route = $this->route()) {
throw new RuntimeException('Unable to generate fingerprint. Route unavailable.');
}

return sha1(implode('|', array_merge(
$route->methods(),
[$route->getDomain(), $route->uri(), $this->ip()]
)));
}







public function setJson($json)
{
$this->json = $json;

return $this;
}






public function getUserResolver()
{
return $this->userResolver ?: function () {

};
}







public function setUserResolver(Closure $callback)
{
$this->userResolver = $callback;

return $this;
}






public function getRouteResolver()
{
return $this->routeResolver ?: function () {

};
}







public function setRouteResolver(Closure $callback)
{
$this->routeResolver = $callback;

return $this;
}






public function toArray(): array
{
return $this->all();
}







public function offsetExists($offset): bool
{
$route = $this->route();

return Arr::has(
$this->all() + ($route ? $route->parameters() : []),
$offset
);
}







public function offsetGet($offset): mixed
{
return $this->__get($offset);
}








public function offsetSet($offset, $value): void
{
$this->getInputSource()->set($offset, $value);
}







public function offsetUnset($offset): void
{
$this->getInputSource()->remove($offset);
}







public function __isset($key)
{
return ! is_null($this->__get($key));
}







public function __get($key)
{
return Arr::get($this->all(), $key, fn () => $this->route($key));
}
}
