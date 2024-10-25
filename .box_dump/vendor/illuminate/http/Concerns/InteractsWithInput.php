<?php

namespace Illuminate\Http\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Traits\Dumpable;
use SplFileInfo;
use stdClass;
use Symfony\Component\HttpFoundation\InputBag;

trait InteractsWithInput
{
use Dumpable;








public function server($key = null, $default = null)
{
return $this->retrieveItem('server', $key, $default);
}







public function hasHeader($key)
{
return ! is_null($this->header($key));
}








public function header($key = null, $default = null)
{
return $this->retrieveItem('headers', $key, $default);
}






public function bearerToken()
{
$header = $this->header('Authorization', '');

$position = strrpos($header, 'Bearer ');

if ($position !== false) {
$header = substr($header, $position + 7);

return str_contains($header, ',') ? strstr($header, ',', true) : $header;
}
}







public function exists($key)
{
return $this->has($key);
}







public function has($key)
{
$keys = is_array($key) ? $key : func_get_args();

$input = $this->all();

foreach ($keys as $value) {
if (! Arr::has($input, $value)) {
return false;
}
}

return true;
}







public function hasAny($keys)
{
$keys = is_array($keys) ? $keys : func_get_args();

$input = $this->all();

return Arr::hasAny($input, $keys);
}









public function whenHas($key, callable $callback, ?callable $default = null)
{
if ($this->has($key)) {
return $callback(data_get($this->all(), $key)) ?: $this;
}

if ($default) {
return $default();
}

return $this;
}







public function filled($key)
{
$keys = is_array($key) ? $key : func_get_args();

foreach ($keys as $value) {
if ($this->isEmptyString($value)) {
return false;
}
}

return true;
}







public function isNotFilled($key)
{
$keys = is_array($key) ? $key : func_get_args();

foreach ($keys as $value) {
if (! $this->isEmptyString($value)) {
return false;
}
}

return true;
}







public function anyFilled($keys)
{
$keys = is_array($keys) ? $keys : func_get_args();

foreach ($keys as $key) {
if ($this->filled($key)) {
return true;
}
}

return false;
}









public function whenFilled($key, callable $callback, ?callable $default = null)
{
if ($this->filled($key)) {
return $callback(data_get($this->all(), $key)) ?: $this;
}

if ($default) {
return $default();
}

return $this;
}







public function missing($key)
{
$keys = is_array($key) ? $key : func_get_args();

return ! $this->has($keys);
}









public function whenMissing($key, callable $callback, ?callable $default = null)
{
if ($this->missing($key)) {
return $callback(data_get($this->all(), $key)) ?: $this;
}

if ($default) {
return $default();
}

return $this;
}







protected function isEmptyString($key)
{
$value = $this->input($key);

return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
}






public function keys()
{
return array_merge(array_keys($this->input()), $this->files->keys());
}







public function all($keys = null)
{
$input = array_replace_recursive($this->input(), $this->allFiles());

if (! $keys) {
return $input;
}

$results = [];

foreach (is_array($keys) ? $keys : func_get_args() as $key) {
Arr::set($results, $key, Arr::get($input, $key));
}

return $results;
}








public function input($key = null, $default = null)
{
return data_get(
$this->getInputSource()->all() + $this->query->all(), $key, $default
);
}








public function str($key, $default = null)
{
return $this->string($key, $default);
}








public function string($key, $default = null)
{
return str($this->input($key, $default));
}










public function boolean($key = null, $default = false)
{
return filter_var($this->input($key, $default), FILTER_VALIDATE_BOOLEAN);
}








public function integer($key, $default = 0)
{
return intval($this->input($key, $default));
}








public function float($key, $default = 0.0)
{
return floatval($this->input($key, $default));
}











public function date($key, $format = null, $tz = null)
{
if ($this->isNotFilled($key)) {
return null;
}

if (is_null($format)) {
return Date::parse($this->input($key), $tz);
}

return Date::createFromFormat($format, $this->input($key), $tz);
}

/**
@template






*/
public function enum($key, $enumClass)
{
if ($this->isNotFilled($key) ||
! enum_exists($enumClass) ||
! method_exists($enumClass, 'tryFrom')) {
return null;
}

return $enumClass::tryFrom($this->input($key));
}







public function collect($key = null)
{
return collect(is_array($key) ? $this->only($key) : $this->input($key));
}







public function only($keys)
{
$results = [];

$input = $this->all();

$placeholder = new stdClass;

foreach (is_array($keys) ? $keys : func_get_args() as $key) {
$value = data_get($input, $key, $placeholder);

if ($value !== $placeholder) {
Arr::set($results, $key, $value);
}
}

return $results;
}







public function except($keys)
{
$keys = is_array($keys) ? $keys : func_get_args();

$results = $this->all();

Arr::forget($results, $keys);

return $results;
}








public function query($key = null, $default = null)
{
return $this->retrieveItem('query', $key, $default);
}








public function post($key = null, $default = null)
{
return $this->retrieveItem('request', $key, $default);
}







public function hasCookie($key)
{
return ! is_null($this->cookie($key));
}








public function cookie($key = null, $default = null)
{
return $this->retrieveItem('cookies', $key, $default);
}






public function allFiles()
{
$files = $this->files->all();

return $this->convertedFiles = $this->convertedFiles ?? $this->convertUploadedFiles($files);
}







protected function convertUploadedFiles(array $files)
{
return array_map(function ($file) {
if (is_null($file) || (is_array($file) && empty(array_filter($file)))) {
return $file;
}

return is_array($file)
? $this->convertUploadedFiles($file)
: UploadedFile::createFromBase($file);
}, $files);
}







public function hasFile($key)
{
if (! is_array($files = $this->file($key))) {
$files = [$files];
}

foreach ($files as $file) {
if ($this->isValidFile($file)) {
return true;
}
}

return false;
}







protected function isValidFile($file)
{
return $file instanceof SplFileInfo && $file->getPath() !== '';
}








public function file($key = null, $default = null)
{
return data_get($this->allFiles(), $key, $default);
}









protected function retrieveItem($source, $key, $default)
{
if (is_null($key)) {
return $this->$source->all();
}

if ($this->$source instanceof InputBag) {
return $this->$source->all()[$key] ?? $default;
}

return $this->$source->get($key, $default);
}







public function dump($keys = [])
{
$keys = is_array($keys) ? $keys : func_get_args();

dump(count($keys) > 0 ? $this->only($keys) : $this->all());

return $this;
}
}
