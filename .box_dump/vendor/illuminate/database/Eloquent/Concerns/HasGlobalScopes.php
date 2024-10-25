<?php

namespace Illuminate\Database\Eloquent\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use ReflectionClass;

trait HasGlobalScopes
{





public static function bootHasGlobalScopes()
{
static::addGlobalScopes(static::resolveGlobalScopeAttributes());
}






public static function resolveGlobalScopeAttributes()
{
$reflectionClass = new ReflectionClass(static::class);

return collect($reflectionClass->getAttributes(ScopedBy::class))
->map(fn ($attribute) => $attribute->getArguments())
->flatten()
->all();
}










public static function addGlobalScope($scope, $implementation = null)
{
if (is_string($scope) && ($implementation instanceof Closure || $implementation instanceof Scope)) {
return static::$globalScopes[static::class][$scope] = $implementation;
} elseif ($scope instanceof Closure) {
return static::$globalScopes[static::class][spl_object_hash($scope)] = $scope;
} elseif ($scope instanceof Scope) {
return static::$globalScopes[static::class][get_class($scope)] = $scope;
} elseif (is_string($scope) && class_exists($scope) && is_subclass_of($scope, Scope::class)) {
return static::$globalScopes[static::class][$scope] = new $scope;
}

throw new InvalidArgumentException('Global scope must be an instance of Closure or Scope or be a class name of a class extending '.Scope::class);
}







public static function addGlobalScopes(array $scopes)
{
foreach ($scopes as $key => $scope) {
if (is_string($key)) {
static::addGlobalScope($key, $scope);
} else {
static::addGlobalScope($scope);
}
}
}







public static function hasGlobalScope($scope)
{
return ! is_null(static::getGlobalScope($scope));
}







public static function getGlobalScope($scope)
{
if (is_string($scope)) {
return Arr::get(static::$globalScopes, static::class.'.'.$scope);
}

return Arr::get(
static::$globalScopes, static::class.'.'.get_class($scope)
);
}






public static function getAllGlobalScopes()
{
return static::$globalScopes;
}







public static function setAllGlobalScopes($scopes)
{
static::$globalScopes = $scopes;
}






public function getGlobalScopes()
{
return Arr::get(static::$globalScopes, static::class, []);
}
}
