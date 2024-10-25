<?php

namespace Illuminate\Database\Connectors;

use Illuminate\Support\Arr;
use PDO;

class SqlServerConnector extends Connector implements ConnectorInterface
{





protected $options = [
PDO::ATTR_CASE => PDO::CASE_NATURAL,
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
PDO::ATTR_STRINGIFY_FETCHES => false,
];







public function connect(array $config)
{
$options = $this->getOptions($config);

$connection = $this->createConnection($this->getDsn($config), $config, $options);

$this->configureIsolationLevel($connection, $config);

return $connection;
}










protected function configureIsolationLevel($connection, array $config)
{
if (! isset($config['isolation_level'])) {
return;
}

$connection->prepare(
"SET TRANSACTION ISOLATION LEVEL {$config['isolation_level']}"
)->execute();
}







protected function getDsn(array $config)
{



if ($this->prefersOdbc($config)) {
return $this->getOdbcDsn($config);
}

if (in_array('sqlsrv', $this->getAvailableDrivers())) {
return $this->getSqlSrvDsn($config);
} else {
return $this->getDblibDsn($config);
}
}







protected function prefersOdbc(array $config)
{
return in_array('odbc', $this->getAvailableDrivers()) &&
($config['odbc'] ?? null) === true;
}







protected function getDblibDsn(array $config)
{
return $this->buildConnectString('dblib', array_merge([
'host' => $this->buildHostString($config, ':'),
'dbname' => $config['database'],
], Arr::only($config, ['appname', 'charset', 'version'])));
}







protected function getOdbcDsn(array $config)
{
return isset($config['odbc_datasource_name'])
? 'odbc:'.$config['odbc_datasource_name'] : '';
}







protected function getSqlSrvDsn(array $config)
{
$arguments = [
'Server' => $this->buildHostString($config, ','),
];

if (isset($config['database'])) {
$arguments['Database'] = $config['database'];
}

if (isset($config['readonly'])) {
$arguments['ApplicationIntent'] = 'ReadOnly';
}

if (isset($config['pooling']) && $config['pooling'] === false) {
$arguments['ConnectionPooling'] = '0';
}

if (isset($config['appname'])) {
$arguments['APP'] = $config['appname'];
}

if (isset($config['encrypt'])) {
$arguments['Encrypt'] = $config['encrypt'];
}

if (isset($config['trust_server_certificate'])) {
$arguments['TrustServerCertificate'] = $config['trust_server_certificate'];
}

if (isset($config['multiple_active_result_sets']) && $config['multiple_active_result_sets'] === false) {
$arguments['MultipleActiveResultSets'] = 'false';
}

if (isset($config['transaction_isolation'])) {
$arguments['TransactionIsolation'] = $config['transaction_isolation'];
}

if (isset($config['multi_subnet_failover'])) {
$arguments['MultiSubnetFailover'] = $config['multi_subnet_failover'];
}

if (isset($config['column_encryption'])) {
$arguments['ColumnEncryption'] = $config['column_encryption'];
}

if (isset($config['key_store_authentication'])) {
$arguments['KeyStoreAuthentication'] = $config['key_store_authentication'];
}

if (isset($config['key_store_principal_id'])) {
$arguments['KeyStorePrincipalId'] = $config['key_store_principal_id'];
}

if (isset($config['key_store_secret'])) {
$arguments['KeyStoreSecret'] = $config['key_store_secret'];
}

if (isset($config['login_timeout'])) {
$arguments['LoginTimeout'] = $config['login_timeout'];
}

if (isset($config['authentication'])) {
$arguments['Authentication'] = $config['authentication'];
}

return $this->buildConnectString('sqlsrv', $arguments);
}








protected function buildConnectString($driver, array $arguments)
{
return $driver.':'.implode(';', array_map(function ($key) use ($arguments) {
return sprintf('%s=%s', $key, $arguments[$key]);
}, array_keys($arguments)));
}








protected function buildHostString(array $config, $separator)
{
if (empty($config['port'])) {
return $config['host'];
}

return $config['host'].$separator.$config['port'];
}






protected function getAvailableDrivers()
{
return PDO::getAvailableDrivers();
}
}
