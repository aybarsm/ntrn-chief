<?php

namespace Illuminate\Database\Connectors;

use PDO;

class MySqlConnector extends Connector implements ConnectorInterface
{






public function connect(array $config)
{
$dsn = $this->getDsn($config);

$options = $this->getOptions($config);




$connection = $this->createConnection($dsn, $config, $options);

if (! empty($config['database'])) {
$connection->exec("use `{$config['database']}`;");
}

$this->configureConnection($connection, $config);

return $connection;
}









protected function getDsn(array $config)
{
return $this->hasSocket($config)
? $this->getSocketDsn($config)
: $this->getHostDsn($config);
}







protected function hasSocket(array $config)
{
return isset($config['unix_socket']) && ! empty($config['unix_socket']);
}







protected function getSocketDsn(array $config)
{
return "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
}







protected function getHostDsn(array $config)
{
return isset($config['port'])
? "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}"
: "mysql:host={$config['host']};dbname={$config['database']}";
}








protected function configureConnection(PDO $connection, array $config)
{
if (isset($config['isolation_level'])) {
$connection->exec(sprintf('SET SESSION TRANSACTION ISOLATION LEVEL %s;', $config['isolation_level']));
}

$statements = [];

if (isset($config['charset'])) {
if (isset($config['collation'])) {
$statements[] = sprintf("NAMES '%s' COLLATE '%s'", $config['charset'], $config['collation']);
} else {
$statements[] = sprintf("NAMES '%s'", $config['charset']);
}
}

if (isset($config['timezone'])) {
$statements[] = sprintf("time_zone='%s'", $config['timezone']);
}

$sqlMode = $this->getSqlMode($connection, $config);

if ($sqlMode !== null) {
$statements[] = sprintf("SESSION sql_mode='%s'", $sqlMode);
}

if ($statements !== []) {
$connection->exec(sprintf('SET %s;', implode(', ', $statements)));
}
}








protected function getSqlMode(PDO $connection, array $config)
{
if (isset($config['modes'])) {
return implode(',', $config['modes']);
}

if (! isset($config['strict'])) {
return null;
}

if (! $config['strict']) {
return 'NO_ENGINE_SUBSTITUTION';
}

$version = $config['version'] ?? $connection->getAttribute(PDO::ATTR_SERVER_VERSION);

if (version_compare($version, '8.0.11') >= 0) {
return 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
}

return 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
}
}
