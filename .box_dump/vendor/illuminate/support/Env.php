<?php

namespace Illuminate\Support;

use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use PhpOption\Option;
use RuntimeException;

class Env
{





protected static $putenv = true;






protected static $repository;






public static function enablePutenv()
{
static::$putenv = true;
static::$repository = null;
}






public static function disablePutenv()
{
static::$putenv = false;
static::$repository = null;
}






public static function getRepository()
{
if (static::$repository === null) {
$builder = RepositoryBuilder::createWithDefaultAdapters();

if (static::$putenv) {
$builder = $builder->addAdapter(PutenvAdapter::class);
}

static::$repository = $builder->immutable()->make();
}

return static::$repository;
}








public static function get($key, $default = null)
{
return self::getOption($key)->getOrCall(fn () => value($default));
}









public static function getOrFail($key)
{
return self::getOption($key)->getOrThrow(new RuntimeException("Environment variable [$key] has no value."));
}







protected static function getOption($key)
{
return Option::fromValue(static::getRepository()->get($key))
->map(function ($value) {
switch (strtolower($value)) {
case 'true':
case '(true)':
return true;
case 'false':
case '(false)':
return false;
case 'empty':
case '(empty)':
return '';
case 'null':
case '(null)':
return;
}

if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
return $matches[2];
}

return $value;
});
}
}
