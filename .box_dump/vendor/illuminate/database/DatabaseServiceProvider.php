<?php

namespace Illuminate\Database;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Contracts\Queue\EntityResolver;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\QueueEntityResolver;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{





protected static $fakers = [];






public function boot()
{
Model::setConnectionResolver($this->app['db']);

Model::setEventDispatcher($this->app['events']);
}






public function register()
{
Model::clearBootedModels();

$this->registerConnectionServices();
$this->registerFakerGenerator();
$this->registerQueueableEntityResolver();
}






protected function registerConnectionServices()
{



$this->app->singleton('db.factory', function ($app) {
return new ConnectionFactory($app);
});




$this->app->singleton('db', function ($app) {
return new DatabaseManager($app, $app['db.factory']);
});

$this->app->bind('db.connection', function ($app) {
return $app['db']->connection();
});

$this->app->bind('db.schema', function ($app) {
return $app['db']->connection()->getSchemaBuilder();
});

$this->app->singleton('db.transactions', function ($app) {
return new DatabaseTransactionsManager;
});
}






protected function registerFakerGenerator()
{
$this->app->singleton(FakerGenerator::class, function ($app, $parameters) {
$locale = $parameters['locale'] ?? $app['config']->get('app.faker_locale', 'en_US');

if (! isset(static::$fakers[$locale])) {
static::$fakers[$locale] = FakerFactory::create($locale);
}

static::$fakers[$locale]->unique(true);

return static::$fakers[$locale];
});
}






protected function registerQueueableEntityResolver()
{
$this->app->singleton(EntityResolver::class, function () {
return new QueueEntityResolver;
});
}
}
