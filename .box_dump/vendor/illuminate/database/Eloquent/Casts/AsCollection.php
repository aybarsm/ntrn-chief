<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class AsCollection implements Castable
{






public static function castUsing(array $arguments)
{
return new class($arguments) implements CastsAttributes
{
public function __construct(protected array $arguments)
{
}

public function get($model, $key, $value, $attributes)
{
if (! isset($attributes[$key])) {
return;
}

$data = Json::decode($attributes[$key]);

$collectionClass = $this->arguments[0] ?? Collection::class;

if (! is_a($collectionClass, Collection::class, true)) {
throw new InvalidArgumentException('The provided class must extend ['.Collection::class.'].');
}

return is_array($data) ? new $collectionClass($data) : null;
}

public function set($model, $key, $value, $attributes)
{
return [$key => Json::encode($value)];
}
};
}







public static function using($class)
{
return static::class.':'.$class;
}
}
