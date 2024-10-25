<?php

namespace Illuminate\Database\Eloquent\Factories;

/**
@template
*/
trait HasFactory
{







public static function factory($count = null, $state = [])
{
$factory = static::newFactory() ?? Factory::factoryForModel(static::class);

return $factory
->count(is_numeric($count) ? $count : null)
->state(is_callable($count) || is_array($count) ? $count : $state);
}






protected static function newFactory()
{
if (isset(static::$factory)) {
return static::$factory::new();
}

return null;
}
}
