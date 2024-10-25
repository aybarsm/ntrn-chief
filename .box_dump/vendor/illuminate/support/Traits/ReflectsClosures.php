<?php

namespace Illuminate\Support\Traits;

use Closure;
use Illuminate\Support\Reflector;
use ReflectionFunction;
use RuntimeException;

trait ReflectsClosures
{









protected function firstClosureParameterType(Closure $closure)
{
$types = array_values($this->closureParameterTypes($closure));

if (! $types) {
throw new RuntimeException('The given Closure has no parameters.');
}

if ($types[0] === null) {
throw new RuntimeException('The first parameter of the given Closure is missing a type hint.');
}

return $types[0];
}










protected function firstClosureParameterTypes(Closure $closure)
{
$reflection = new ReflectionFunction($closure);

$types = collect($reflection->getParameters())->mapWithKeys(function ($parameter) {
if ($parameter->isVariadic()) {
return [$parameter->getName() => null];
}

return [$parameter->getName() => Reflector::getParameterClassNames($parameter)];
})->filter()->values()->all();

if (empty($types)) {
throw new RuntimeException('The given Closure has no parameters.');
}

if (isset($types[0]) && empty($types[0])) {
throw new RuntimeException('The first parameter of the given Closure is missing a type hint.');
}

return $types[0];
}









protected function closureParameterTypes(Closure $closure)
{
$reflection = new ReflectionFunction($closure);

return collect($reflection->getParameters())->mapWithKeys(function ($parameter) {
if ($parameter->isVariadic()) {
return [$parameter->getName() => null];
}

return [$parameter->getName() => Reflector::getParameterClassName($parameter)];
})->all();
}
}
