<?php

namespace Illuminate\Support\Facades;

use Illuminate\Filesystem\Filesystem;

















































































class Storage extends Facade
{







public static function fake($disk = null, array $config = [])
{
$disk = $disk ?: static::$app['config']->get('filesystems.default');

$root = storage_path('framework/testing/disks/'.$disk);

if ($token = ParallelTesting::token()) {
$root = "{$root}_test_{$token}";
}

(new Filesystem)->cleanDirectory($root);

static::set($disk, $fake = static::createLocalDriver(array_merge($config, [
'root' => $root,
])));

return tap($fake)->buildTemporaryUrlsUsing(function ($path, $expiration) {
return URL::to($path.'?expiration='.$expiration->getTimestamp());
});
}








public static function persistentFake($disk = null, array $config = [])
{
$disk = $disk ?: static::$app['config']->get('filesystems.default');

static::set($disk, $fake = static::createLocalDriver(array_merge($config, [
'root' => storage_path('framework/testing/disks/'.$disk),
])));

return $fake;
}






protected static function getFacadeAccessor()
{
return 'filesystem';
}
}
