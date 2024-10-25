<?php

namespace Illuminate\Support;

use ArrayIterator;
use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Support\Facades\Date;
use stdClass;
use Symfony\Component\VarDumper\VarDumper;
use Traversable;

class ValidatedInput implements ValidatedData
{





protected $input;







public function __construct(array $input)
{
$this->input = $input;
}







public function exists($key)
{
return $this->has($key);
}







public function has($keys)
{
$keys = is_array($keys) ? $keys : func_get_args();

foreach ($keys as $key) {
if (! Arr::has($this->all(), $key)) {
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







public function missing($keys)
{
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








public function str($key, $default = null)
{
return $this->string($key, $default);
}








public function string($key, $default = null)
{
return str($this->input($key, $default));
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







public function merge(array $items)
{
return new static(array_merge($this->all(), $items));
}







public function collect($key = null)
{
return collect(is_array($key) ? $this->only($key) : $this->input($key));
}






public function all()
{
return $this->input;
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







protected function isEmptyString($key)
{
$value = $this->input($key);

return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
}






public function keys()
{
return array_keys($this->input());
}








public function input($key = null, $default = null)
{
return data_get(
$this->all(), $key, $default
);
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







public function dd(...$keys)
{
$this->dump(...$keys);

exit(1);
}







public function dump($keys = [])
{
$keys = is_array($keys) ? $keys : func_get_args();

VarDumper::dump(count($keys) > 0 ? $this->only($keys) : $this->all());

return $this;
}






public function toArray()
{
return $this->all();
}







public function __get($name)
{
return $this->input($name);
}








public function __set($name, $value)
{
$this->input[$name] = $value;
}






public function __isset($name)
{
return $this->exists($name);
}







public function __unset($name)
{
unset($this->input[$name]);
}







public function offsetExists($key): bool
{
return $this->exists($key);
}







public function offsetGet($key): mixed
{
return $this->input($key);
}








public function offsetSet($key, $value): void
{
if (is_null($key)) {
$this->input[] = $value;
} else {
$this->input[$key] = $value;
}
}







public function offsetUnset($key): void
{
unset($this->input[$key]);
}






public function getIterator(): Traversable
{
return new ArrayIterator($this->input);
}
}
