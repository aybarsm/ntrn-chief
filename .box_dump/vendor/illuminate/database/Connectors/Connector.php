<?php

namespace Illuminate\Database\Connectors;

use Exception;
use Illuminate\Database\DetectsLostConnections;
use PDO;
use Throwable;

class Connector
{
use DetectsLostConnections;






protected $options = [
PDO::ATTR_CASE => PDO::CASE_NATURAL,
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
PDO::ATTR_STRINGIFY_FETCHES => false,
PDO::ATTR_EMULATE_PREPARES => false,
];











public function createConnection($dsn, array $config, array $options)
{
[$username, $password] = [
$config['username'] ?? null, $config['password'] ?? null,
];

try {
return $this->createPdoConnection(
$dsn, $username, $password, $options
);
} catch (Exception $e) {
return $this->tryAgainIfCausedByLostConnection(
$e, $dsn, $username, $password, $options
);
}
}










protected function createPdoConnection($dsn, $username, $password, $options)
{
return version_compare(phpversion(), '8.4.0', '<')
? new PDO($dsn, $username, $password, $options)
: PDO::connect($dsn, $username, $password, $options); /**
@phpstan-ignore */
}













protected function tryAgainIfCausedByLostConnection(Throwable $e, $dsn, $username, $password, $options)
{
if ($this->causedByLostConnection($e)) {
return $this->createPdoConnection($dsn, $username, $password, $options);
}

throw $e;
}







public function getOptions(array $config)
{
$options = $config['options'] ?? [];

return array_diff_key($this->options, $options) + $options;
}






public function getDefaultOptions()
{
return $this->options;
}







public function setDefaultOptions(array $options)
{
$this->options = $options;
}
}
