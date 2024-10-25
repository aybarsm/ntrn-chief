<?php

namespace Illuminate\Session;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{





public function register()
{
$this->registerSessionManager();

$this->registerSessionDriver();

$this->app->singleton(StartSession::class, function ($app) {
return new StartSession($app->make(SessionManager::class), function () use ($app) {
return $app->make(CacheFactory::class);
});
});
}






protected function registerSessionManager()
{
$this->app->singleton('session', function ($app) {
return new SessionManager($app);
});
}






protected function registerSessionDriver()
{
$this->app->singleton('session.store', function ($app) {



return $app->make('session')->driver();
});
}
}
