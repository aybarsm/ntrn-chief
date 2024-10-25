<?php

namespace Illuminate\Http\Resources;

use Exception;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;

trait DelegatesToResource
{
use ForwardsCalls, Macroable {
__call as macroCall;
}






public function getRouteKey()
{
return $this->resource->getRouteKey();
}






public function getRouteKeyName()
{
return $this->resource->getRouteKeyName();
}










public function resolveRouteBinding($value, $field = null)
{
throw new Exception('Resources may not be implicitly resolved from route bindings.');
}











public function resolveChildRouteBinding($childType, $value, $field = null)
{
throw new Exception('Resources may not be implicitly resolved from route bindings.');
}







public function offsetExists($offset): bool
{
return isset($this->resource[$offset]);
}







public function offsetGet($offset): mixed
{
return $this->resource[$offset];
}








public function offsetSet($offset, $value): void
{
$this->resource[$offset] = $value;
}







public function offsetUnset($offset): void
{
unset($this->resource[$offset]);
}







public function __isset($key)
{
return isset($this->resource->{$key});
}







public function __unset($key)
{
unset($this->resource->{$key});
}







public function __get($key)
{
return $this->resource->{$key};
}








public function __call($method, $parameters)
{
if (static::hasMacro($method)) {
return $this->macroCall($method, $parameters);
}

return $this->forwardCallTo($this->resource, $method, $parameters);
}
}
