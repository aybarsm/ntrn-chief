<?php

namespace Illuminate\Http\Client;

use ArrayAccess;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;
use LogicException;

class Request implements ArrayAccess
{
use Macroable;






protected $request;






protected $data;







public function __construct($request)
{
$this->request = $request;
}






public function method()
{
return $this->request->getMethod();
}






public function url()
{
return (string) $this->request->getUri();
}








public function hasHeader($key, $value = null)
{
if (is_null($value)) {
return ! empty($this->request->getHeaders()[$key]);
}

$headers = $this->headers();

if (! Arr::has($headers, $key)) {
return false;
}

$value = is_array($value) ? $value : [$value];

return empty(array_diff($value, $headers[$key]));
}







public function hasHeaders($headers)
{
if (is_string($headers)) {
$headers = [$headers => null];
}

foreach ($headers as $key => $value) {
if (! $this->hasHeader($key, $value)) {
return false;
}
}

return true;
}







public function header($key)
{
return Arr::get($this->headers(), $key, []);
}






public function headers()
{
return $this->request->getHeaders();
}






public function body()
{
return (string) $this->request->getBody();
}









public function hasFile($name, $value = null, $filename = null)
{
if (! $this->isMultipart()) {
return false;
}

return collect($this->data)->reject(function ($file) use ($name, $value, $filename) {
return $file['name'] != $name ||
($value && $file['contents'] != $value) ||
($filename && $file['filename'] != $filename);
})->count() > 0;
}






public function data()
{
if ($this->isForm()) {
return $this->parameters();
} elseif ($this->isJson()) {
return $this->json();
}

return $this->data ?? [];
}






protected function parameters()
{
if (! $this->data) {
parse_str($this->body(), $parameters);

$this->data = $parameters;
}

return $this->data;
}






protected function json()
{
if (! $this->data) {
$this->data = json_decode($this->body(), true) ?? [];
}

return $this->data;
}






public function isForm()
{
return $this->hasHeader('Content-Type', 'application/x-www-form-urlencoded');
}






public function isJson()
{
return $this->hasHeader('Content-Type') &&
str_contains($this->header('Content-Type')[0], 'json');
}






public function isMultipart()
{
return $this->hasHeader('Content-Type') &&
str_contains($this->header('Content-Type')[0], 'multipart');
}







public function withData(array $data)
{
$this->data = $data;

return $this;
}






public function toPsrRequest()
{
return $this->request;
}







public function offsetExists($offset): bool
{
return isset($this->data()[$offset]);
}







public function offsetGet($offset): mixed
{
return $this->data()[$offset];
}










public function offsetSet($offset, $value): void
{
throw new LogicException('Request data may not be mutated using array access.');
}









public function offsetUnset($offset): void
{
throw new LogicException('Request data may not be mutated using array access.');
}
}
