<?php

namespace Illuminate\Database\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

class AsEncryptedArrayObject implements Castable
{






public static function castUsing(array $arguments)
{
return new class implements CastsAttributes
{
public function get($model, $key, $value, $attributes)
{
if (isset($attributes[$key])) {
return new ArrayObject(Json::decode(Crypt::decryptString($attributes[$key])));
}

return null;
}

public function set($model, $key, $value, $attributes)
{
if (! is_null($value)) {
return [$key => Crypt::encryptString(Json::encode($value))];
}

return null;
}

public function serialize($model, string $key, $value, array $attributes)
{
return ! is_null($value) ? $value->getArrayCopy() : null;
}
};
}
}
