<?php

namespace Illuminate\Support;

/**
@template
@template-covariant
@mixin
@mixin


*/
class HigherOrderCollectionProxy
{





protected $collection;






protected $method;








public function __construct(Enumerable $collection, $method)
{
$this->method = $method;
$this->collection = $collection;
}







public function __get($key)
{
return $this->collection->{$this->method}(function ($value) use ($key) {
return is_array($value) ? $value[$key] : $value->{$key};
});
}








public function __call($method, $parameters)
{
return $this->collection->{$this->method}(function ($value) use ($method, $parameters) {
return $value->{$method}(...$parameters);
});
}
}
