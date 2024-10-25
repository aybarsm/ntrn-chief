<?php

namespace Illuminate\Redis;

use Closure;
use Illuminate\Contracts\Redis\Factory;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Redis\Connectors\PhpRedisConnector;
use Illuminate\Redis\Connectors\PredisConnector;
use Illuminate\Support\Arr;
use Illuminate\Support\ConfigurationUrlParser;
use InvalidArgumentException;

/**
@mixin
*/
class RedisManager implements Factory
{





protected $app;






protected $driver;






protected $customCreators = [];






protected $config;






protected $connections;






protected $events = false;









public function __construct($app, $driver, array $config)
{
$this->app = $app;
$this->driver = $driver;
$this->config = $config;
}







public function connection($name = null)
{
$name = $name ?: 'default';

if (isset($this->connections[$name])) {
return $this->connections[$name];
}

return $this->connections[$name] = $this->configure(
$this->resolve($name), $name
);
}









public function resolve($name = null)
{
$name = $name ?: 'default';

$options = $this->config['options'] ?? [];

if (isset($this->config[$name])) {
return $this->connector()->connect(
$this->parseConnectionConfiguration($this->config[$name]),
array_merge(Arr::except($options, 'parameters'), ['parameters' => Arr::get($options, 'parameters.'.$name, Arr::get($options, 'parameters', []))])
);
}

if (isset($this->config['clusters'][$name])) {
return $this->resolveCluster($name);
}

throw new InvalidArgumentException("Redis connection [{$name}] not configured.");
}







protected function resolveCluster($name)
{
return $this->connector()->connectToCluster(
array_map(function ($config) {
return $this->parseConnectionConfiguration($config);
}, $this->config['clusters'][$name]),
$this->config['clusters']['options'] ?? [],
$this->config['options'] ?? []
);
}








protected function configure(Connection $connection, $name)
{
$connection->setName($name);

if ($this->events && $this->app->bound('events')) {
$connection->setEventDispatcher($this->app->make('events'));
}

return $connection;
}






protected function connector()
{
$customCreator = $this->customCreators[$this->driver] ?? null;

if ($customCreator) {
return $customCreator();
}

return match ($this->driver) {
'predis' => new PredisConnector,
'phpredis' => new PhpRedisConnector,
default => null,
};
}







protected function parseConnectionConfiguration($config)
{
$parsed = (new ConfigurationUrlParser)->parseConfiguration($config);

$driver = strtolower($parsed['driver'] ?? '');

if (in_array($driver, ['tcp', 'tls'])) {
$parsed['scheme'] = $driver;
}

return array_filter($parsed, function ($key) {
return $key !== 'driver';
}, ARRAY_FILTER_USE_KEY);
}






public function connections()
{
return $this->connections;
}






public function enableEvents()
{
$this->events = true;
}






public function disableEvents()
{
$this->events = false;
}







public function setDriver($driver)
{
$this->driver = $driver;
}







public function purge($name = null)
{
$name = $name ?: 'default';

unset($this->connections[$name]);
}








public function extend($driver, Closure $callback)
{
$this->customCreators[$driver] = $callback->bindTo($this, $this);

return $this;
}








public function __call($method, $parameters)
{
return $this->connection()->{$method}(...$parameters);
}
}
