<?php

namespace Illuminate\Database\Connectors;

use Illuminate\Database\Concerns\ParsesSearchPath;
use PDO;

class PostgresConnector extends Connector implements ConnectorInterface
{
use ParsesSearchPath;






protected $options = [
PDO::ATTR_CASE => PDO::CASE_NATURAL,
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
PDO::ATTR_STRINGIFY_FETCHES => false,
];







public function connect(array $config)
{



$connection = $this->createConnection(
$this->getDsn($config), $config, $this->getOptions($config)
);

$this->configureIsolationLevel($connection, $config);




$this->configureTimezone($connection, $config);

$this->configureSearchPath($connection, $config);

$this->configureSynchronousCommit($connection, $config);

return $connection;
}








protected function configureIsolationLevel($connection, array $config)
{
if (isset($config['isolation_level'])) {
$connection->prepare("set session characteristics as transaction isolation level {$config['isolation_level']}")->execute();
}
}








protected function configureTimezone($connection, array $config)
{
if (isset($config['timezone'])) {
$timezone = $config['timezone'];

$connection->prepare("set time zone '{$timezone}'")->execute();
}
}








protected function configureSearchPath($connection, $config)
{
if (isset($config['search_path']) || isset($config['schema'])) {
$searchPath = $this->quoteSearchPath(
$this->parseSearchPath($config['search_path'] ?? $config['schema'])
);

$connection->prepare("set search_path to {$searchPath}")->execute();
}
}







protected function quoteSearchPath($searchPath)
{
return count($searchPath) === 1 ? '"'.$searchPath[0].'"' : '"'.implode('", "', $searchPath).'"';
}







protected function getDsn(array $config)
{



extract($config, EXTR_SKIP);

$host = isset($host) ? "host={$host};" : '';




$database = $connect_via_database ?? $database ?? null;
$port = $connect_via_port ?? $port ?? null;

$dsn = "pgsql:{$host}dbname='{$database}'";




if (! is_null($port)) {
$dsn .= ";port={$port}";
}

if (isset($charset)) {
$dsn .= ";client_encoding='{$charset}'";
}




if (isset($application_name)) {
$dsn .= ";application_name='".str_replace("'", "\'", $application_name)."'";
}

return $this->addSslOptions($dsn, $config);
}








protected function addSslOptions($dsn, array $config)
{
foreach (['sslmode', 'sslcert', 'sslkey', 'sslrootcert'] as $option) {
if (isset($config[$option])) {
$dsn .= ";{$option}={$config[$option]}";
}
}

return $dsn;
}








protected function configureSynchronousCommit($connection, array $config)
{
if (! isset($config['synchronous_commit'])) {
return;
}

$connection->prepare("set synchronous_commit to '{$config['synchronous_commit']}'")->execute();
}
}
