<?php

namespace Illuminate\Redis;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class RedisServiceProvider extends ServiceProvider implements DeferrableProvider
{





public function register()
{
$this->app->singleton('redis', function ($app) {
$config = $app->make('config')->get('database.redis', []);

return new RedisManager($app, Arr::pull($config, 'client', 'phpredis'), $config);
});

$this->app->bind('redis.connection', function ($app) {
return $app['redis']->connection();
});
}






public function provides()
{
return ['redis', 'redis.connection'];
}
}
