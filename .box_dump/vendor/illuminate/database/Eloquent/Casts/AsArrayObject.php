<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AsArrayObject implements Castable
{






public static function castUsing(array $arguments)
{
return new class implements CastsAttributes
{
public function get($model, $key, $value, $attributes)
{
if (! isset($attributes[$key])) {
return;
}

$data = Json::decode($attributes[$key]);

return is_array($data) ? new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS) : null;
}

public function set($model, $key, $value, $attributes)
{
return [$key => Json::encode($value)];
}

public function serialize($model, string $key, $value, array $attributes)
{
return $value->getArrayCopy();
}
};
}
}
