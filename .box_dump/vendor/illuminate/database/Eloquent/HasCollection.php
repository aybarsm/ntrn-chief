<?php

namespace Illuminate\Database\Eloquent;

use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use ReflectionClass;

/**
@template
*/
trait HasCollection
{





protected static array $resolvedCollectionClasses = [];







public function newCollection(array $models = [])
{
static::$resolvedCollectionClasses[static::class] ??= ($this->resolveCollectionFromAttribute() ?? static::$collectionClass);

return new static::$resolvedCollectionClasses[static::class]($models);
}






public function resolveCollectionFromAttribute()
{
$reflectionClass = new ReflectionClass(static::class);

$attributes = $reflectionClass->getAttributes(CollectedBy::class);

if (! isset($attributes[0]) || ! isset($attributes[0]->getArguments()[0])) {
return;
}

return $attributes[0]->getArguments()[0];
}
}
