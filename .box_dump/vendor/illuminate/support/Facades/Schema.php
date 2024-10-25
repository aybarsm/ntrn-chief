<?php

namespace Illuminate\Support\Facades;















































class Schema extends Facade
{





protected static $cached = false;







public static function connection($name)
{
return static::$app['db']->connection($name)->getSchemaBuilder();
}






protected static function getFacadeAccessor()
{
return 'db.schema';
}
}
