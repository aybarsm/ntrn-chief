<?php

namespace Illuminate\Database\Connectors;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\MariaDbConnection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use PDOException;

class ConnectionFactory
{





protected $container;







public function __construct(Container $container)
{
$this->container = $container;
}








public function make(array $config, $name = null)
{
$config = $this->parseConfig($config, $name);

if (isset($config['read'])) {
return $this->createReadWriteConnection($config);
}

return $this->createSingleConnection($config);
}








protected function parseConfig(array $config, $name)
{
return Arr::add(Arr::add($config, 'prefix', ''), 'name', $name);
}







protected function createSingleConnection(array $config)
{
$pdo = $this->createPdoResolver($config);

return $this->createConnection(
$config['driver'], $pdo, $config['database'], $config['prefix'], $config
);
}







protected function createReadWriteConnection(array $config)
{
$connection = $this->createSingleConnection($this->getWriteConfig($config));

return $connection->setReadPdo($this->createReadPdo($config));
}







protected function createReadPdo(array $config)
{
return $this->createPdoResolver($this->getReadConfig($config));
}







protected function getReadConfig(array $config)
{
return $this->mergeReadWriteConfig(
$config, $this->getReadWriteConfig($config, 'read')
);
}







protected function getWriteConfig(array $config)
{
return $this->mergeReadWriteConfig(
$config, $this->getReadWriteConfig($config, 'write')
);
}








protected function getReadWriteConfig(array $config, $type)
{
return isset($config[$type][0])
? Arr::random($config[$type])
: $config[$type];
}








protected function mergeReadWriteConfig(array $config, array $merge)
{
return Arr::except(array_merge($config, $merge), ['read', 'write']);
}







protected function createPdoResolver(array $config)
{
return array_key_exists('host', $config)
? $this->createPdoResolverWithHosts($config)
: $this->createPdoResolverWithoutHosts($config);
}









protected function createPdoResolverWithHosts(array $config)
{
return function () use ($config) {
foreach (Arr::shuffle($this->parseHosts($config)) as $host) {
$config['host'] = $host;

try {
return $this->createConnector($config)->connect($config);
} catch (PDOException $e) {
continue;
}
}

if (isset($e)) {
throw $e;
}
};
}









protected function parseHosts(array $config)
{
$hosts = Arr::wrap($config['host']);

if (empty($hosts)) {
throw new InvalidArgumentException('Database hosts array is empty.');
}

return $hosts;
}







protected function createPdoResolverWithoutHosts(array $config)
{
return fn () => $this->createConnector($config)->connect($config);
}









public function createConnector(array $config)
{
if (! isset($config['driver'])) {
throw new InvalidArgumentException('A driver must be specified.');
}

if ($this->container->bound($key = "db.connector.{$config['driver']}")) {
return $this->container->make($key);
}

return match ($config['driver']) {
'mysql' => new MySqlConnector,
'mariadb' => new MariaDbConnector,
'pgsql' => new PostgresConnector,
'sqlite' => new SQLiteConnector,
'sqlsrv' => new SqlServerConnector,
default => throw new InvalidArgumentException("Unsupported driver [{$config['driver']}]."),
};
}













protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
{
if ($resolver = Connection::getResolver($driver)) {
return $resolver($connection, $database, $prefix, $config);
}

return match ($driver) {
'mysql' => new MySqlConnection($connection, $database, $prefix, $config),
'mariadb' => new MariaDbConnection($connection, $database, $prefix, $config),
'pgsql' => new PostgresConnection($connection, $database, $prefix, $config),
'sqlite' => new SQLiteConnection($connection, $database, $prefix, $config),
'sqlsrv' => new SqlServerConnection($connection, $database, $prefix, $config),
default => throw new InvalidArgumentException("Unsupported driver [{$driver}]."),
};
}
}
