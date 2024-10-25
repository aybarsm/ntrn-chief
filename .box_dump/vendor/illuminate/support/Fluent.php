<?php

namespace Illuminate\Support;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

/**
@template
@template
@implements
@implements

*/
class Fluent implements Arrayable, ArrayAccess, Jsonable, JsonSerializable
{





protected $attributes = [];







public function __construct($attributes = [])
{
foreach ($attributes as $key => $value) {
$this->attributes[$key] = $value;
}
}

/**
@template






*/
public function get($key, $default = null)
{
return data_get($this->attributes, $key, $default);
}








public function value($key, $default = null)
{
if (array_key_exists($key, $this->attributes)) {
return $this->attributes[$key];
}

return value($default);
}








public function scope($key, $default = null)
{
return new static(
(array) $this->get($key, $default)
);
}






public function getAttributes()
{
return $this->attributes;
}






public function toArray()
{
return $this->attributes;
}







public function collect($key = null)
{
return new Collection($this->get($key));
}






public function jsonSerialize(): array
{
return $this->toArray();
}







public function toJson($options = 0)
{
return json_encode($this->jsonSerialize(), $options);
}







public function offsetExists($offset): bool
{
return isset($this->attributes[$offset]);
}







public function offsetGet($offset): mixed
{
return $this->value($offset);
}








public function offsetSet($offset, $value): void
{
$this->attributes[$offset] = $value;
}







public function offsetUnset($offset): void
{
unset($this->attributes[$offset]);
}








public function __call($method, $parameters)
{
$this->attributes[$method] = count($parameters) > 0 ? reset($parameters) : true;

return $this;
}







public function __get($key)
{
return $this->value($key);
}








public function __set($key, $value)
{
$this->offsetSet($key, $value);
}







public function __isset($key)
{
return $this->offsetExists($key);
}







public function __unset($key)
{
$this->offsetUnset($key);
}
}
