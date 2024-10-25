<?php

namespace Illuminate\Log;

use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{





public function register()
{
$this->app->singleton('log', fn ($app) => new LogManager($app));
}
}
