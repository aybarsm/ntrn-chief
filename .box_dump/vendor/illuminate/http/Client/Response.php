<?php

namespace Illuminate\Http\Client;

use ArrayAccess;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use LogicException;
use Stringable;

/**
@mixin
*/
class Response implements ArrayAccess, Stringable
{
use Concerns\DeterminesStatusCode, Macroable {
__call as macroCall;
}






protected $response;






protected $decoded;






public $cookies;






public $transferStats;







public function __construct($response)
{
$this->response = $response;
}






public function body()
{
return (string) $this->response->getBody();
}








public function json($key = null, $default = null)
{
if (! $this->decoded) {
$this->decoded = json_decode($this->body(), true);
}

if (is_null($key)) {
return $this->decoded;
}

return data_get($this->decoded, $key, $default);
}






public function object()
{
return json_decode($this->body(), false);
}







public function collect($key = null)
{
return Collection::make($this->json($key));
}








public function resource()
{
return StreamWrapper::getResource($this->response->getBody());
}







public function header(string $header)
{
return $this->response->getHeaderLine($header);
}






public function headers()
{
return $this->response->getHeaders();
}






public function status()
{
return (int) $this->response->getStatusCode();
}






public function reason()
{
return $this->response->getReasonPhrase();
}






public function effectiveUri()
{
return $this->transferStats?->getEffectiveUri();
}






public function successful()
{
return $this->status() >= 200 && $this->status() < 300;
}






public function redirect()
{
return $this->status() >= 300 && $this->status() < 400;
}






public function failed()
{
return $this->serverError() || $this->clientError();
}






public function clientError()
{
return $this->status() >= 400 && $this->status() < 500;
}






public function serverError()
{
return $this->status() >= 500;
}







public function onError(callable $callback)
{
if ($this->failed()) {
$callback($this);
}

return $this;
}






public function cookies()
{
return $this->cookies;
}






public function handlerStats()
{
return $this->transferStats?->getHandlerStats() ?? [];
}






public function close()
{
$this->response->getBody()->close();

return $this;
}






public function toPsrResponse()
{
return $this->response;
}






public function toException()
{
if ($this->failed()) {
return new RequestException($this);
}
}








public function throw()
{
$callback = func_get_args()[0] ?? null;

if ($this->failed()) {
throw tap($this->toException(), function ($exception) use ($callback) {
if ($callback && is_callable($callback)) {
$callback($this, $exception);
}
});
}

return $this;
}









public function throwIf($condition)
{
return value($condition, $this) ? $this->throw(func_get_args()[1] ?? null) : $this;
}









public function throwIfStatus($statusCode)
{
if (is_callable($statusCode) &&
$statusCode($this->status(), $this)) {
return $this->throw();
}

return $this->status() === $statusCode ? $this->throw() : $this;
}









public function throwUnlessStatus($statusCode)
{
if (is_callable($statusCode)) {
return $statusCode($this->status(), $this) ? $this : $this->throw();
}

return $this->status() === $statusCode ? $this : $this->throw();
}








public function throwIfClientError()
{
return $this->clientError() ? $this->throw() : $this;
}








public function throwIfServerError()
{
return $this->serverError() ? $this->throw() : $this;
}







public function offsetExists($offset): bool
{
return isset($this->json()[$offset]);
}







public function offsetGet($offset): mixed
{
return $this->json()[$offset];
}










public function offsetSet($offset, $value): void
{
throw new LogicException('Response data may not be mutated using array access.');
}









public function offsetUnset($offset): void
{
throw new LogicException('Response data may not be mutated using array access.');
}






public function __toString()
{
return $this->body();
}








public function __call($method, $parameters)
{
return static::hasMacro($method)
? $this->macroCall($method, $parameters)
: $this->response->{$method}(...$parameters);
}
}
