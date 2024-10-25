<?php

namespace Illuminate\Database;

use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Arr;
use Illuminate\Support\ConfigurationUrlParser;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
@mixin
*/
class DatabaseManager implements ConnectionResolverInterface
{
use Macroable {
__call as macroCall;
}






protected $app;






protected $factory;






protected $connections = [];






protected $extensions = [];






protected $reconnector;








public function __construct($app, ConnectionFactory $factory)
{
$this->app = $app;
$this->factory = $factory;

$this->reconnector = function ($connection) {
$this->reconnect($connection->getNameWithReadWriteType());
};
}







public function connection($name = null)
{
$name = $name ?: $this->getDefaultConnection();

[$database, $type] = $this->parseConnectionName($name);




if (! isset($this->connections[$name])) {
$this->connections[$name] = $this->configure(
$this->makeConnection($database), $type
);

$this->dispatchConnectionEstablishedEvent($this->connections[$name]);
}

return $this->connections[$name];
}









public function connectUsing(string $name, array $config, bool $force = false)
{
if ($force) {
$this->purge($name);
}

if (isset($this->connections[$name])) {
throw new RuntimeException("Cannot establish connection [$name] because another connection with that name already exists.");
}

$connection = $this->configure(
$this->factory->make($config, $name), null
);

$this->dispatchConnectionEstablishedEvent($connection);

return tap($connection, fn ($connection) => $this->connections[$name] = $connection);
}







protected function parseConnectionName($name)
{
$name = $name ?: $this->getDefaultConnection();

return Str::endsWith($name, ['::read', '::write'])
? explode('::', $name, 2) : [$name, null];
}







protected function makeConnection($name)
{
$config = $this->configuration($name);




if (isset($this->extensions[$name])) {
return call_user_func($this->extensions[$name], $config, $name);
}




if (isset($this->extensions[$driver = $config['driver']])) {
return call_user_func($this->extensions[$driver], $config, $name);
}

return $this->factory->make($config, $name);
}









protected function configuration($name)
{
$name = $name ?: $this->getDefaultConnection();




$connections = $this->app['config']['database.connections'];

if (is_null($config = Arr::get($connections, $name))) {
throw new InvalidArgumentException("Database connection [{$name}] not configured.");
}

return (new ConfigurationUrlParser)
->parseConfiguration($config);
}








protected function configure(Connection $connection, $type)
{
$connection = $this->setPdoForType($connection, $type)->setReadWriteType($type);




if ($this->app->bound('events')) {
$connection->setEventDispatcher($this->app['events']);
}

if ($this->app->bound('db.transactions')) {
$connection->setTransactionManager($this->app['db.transactions']);
}




$connection->setReconnector($this->reconnector);

return $connection;
}







protected function dispatchConnectionEstablishedEvent(Connection $connection)
{
if (! $this->app->bound('events')) {
return;
}

$this->app['events']->dispatch(
new ConnectionEstablished($connection)
);
}








protected function setPdoForType(Connection $connection, $type = null)
{
if ($type === 'read') {
$connection->setPdo($connection->getReadPdo());
} elseif ($type === 'write') {
$connection->setReadPdo($connection->getPdo());
}

return $connection;
}







public function purge($name = null)
{
$name = $name ?: $this->getDefaultConnection();

$this->disconnect($name);

unset($this->connections[$name]);
}







public function disconnect($name = null)
{
if (isset($this->connections[$name = $name ?: $this->getDefaultConnection()])) {
$this->connections[$name]->disconnect();
}
}







public function reconnect($name = null)
{
$this->disconnect($name = $name ?: $this->getDefaultConnection());

if (! isset($this->connections[$name])) {
return $this->connection($name);
}

return $this->refreshPdoConnections($name);
}








public function usingConnection($name, callable $callback)
{
$previousName = $this->getDefaultConnection();

$this->setDefaultConnection($name);

return tap($callback(), function () use ($previousName) {
$this->setDefaultConnection($previousName);
});
}







protected function refreshPdoConnections($name)
{
[$database, $type] = $this->parseConnectionName($name);

$fresh = $this->configure(
$this->makeConnection($database), $type
);

return $this->connections[$name]
->setPdo($fresh->getRawPdo())
->setReadPdo($fresh->getRawReadPdo());
}






public function getDefaultConnection()
{
return $this->app['config']['database.default'];
}







public function setDefaultConnection($name)
{
$this->app['config']['database.default'] = $name;
}






public function supportedDrivers()
{
return ['mysql', 'mariadb', 'pgsql', 'sqlite', 'sqlsrv'];
}






public function availableDrivers()
{
return array_intersect(
$this->supportedDrivers(),
str_replace('dblib', 'sqlsrv', PDO::getAvailableDrivers())
);
}








public function extend($name, callable $resolver)
{
$this->extensions[$name] = $resolver;
}







public function forgetExtension($name)
{
unset($this->extensions[$name]);
}






public function getConnections()
{
return $this->connections;
}







public function setReconnector(callable $reconnector)
{
$this->reconnector = $reconnector;
}







public function setApplication($app)
{
$this->app = $app;

return $this;
}








public function __call($method, $parameters)
{
if (static::hasMacro($method)) {
return $this->macroCall($method, $parameters);
}

return $this->connection()->$method(...$parameters);
}
}
