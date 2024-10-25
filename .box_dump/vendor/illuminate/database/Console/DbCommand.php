<?php

namespace Illuminate\Database\Console;

use Illuminate\Console\Command;
use Illuminate\Support\ConfigurationUrlParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\Process;
use UnexpectedValueException;

#[AsCommand(name: 'db')]
class DbCommand extends Command
{





protected $signature = 'db {connection? : The database connection that should be used}
               {--read : Connect to the read connection}
               {--write : Connect to the write connection}';






protected $description = 'Start a new database CLI session';






public function handle()
{
$connection = $this->getConnection();

if (! isset($connection['host']) && $connection['driver'] !== 'sqlite') {
$this->components->error('No host specified for this database connection.');
$this->line('  Use the <options=bold>[--read]</> and <options=bold>[--write]</> options to specify a read or write connection.');
$this->newLine();

return Command::FAILURE;
}

(new Process(
array_merge([$this->getCommand($connection)], $this->commandArguments($connection)),
null,
$this->commandEnvironment($connection)
))->setTimeout(null)->setTty(true)->mustRun(function ($type, $buffer) {
$this->output->write($buffer);
});

return 0;
}








public function getConnection()
{
$connection = $this->laravel['config']['database.connections.'.
(($db = $this->argument('connection')) ?? $this->laravel['config']['database.default'])
];

if (empty($connection)) {
throw new UnexpectedValueException("Invalid database connection [{$db}].");
}

if (! empty($connection['url'])) {
$connection = (new ConfigurationUrlParser)->parseConfiguration($connection);
}

if ($this->option('read')) {
if (is_array($connection['read']['host'])) {
$connection['read']['host'] = $connection['read']['host'][0];
}

$connection = array_merge($connection, $connection['read']);
} elseif ($this->option('write')) {
if (is_array($connection['write']['host'])) {
$connection['write']['host'] = $connection['write']['host'][0];
}

$connection = array_merge($connection, $connection['write']);
}

return $connection;
}







public function commandArguments(array $connection)
{
$driver = ucfirst($connection['driver']);

return $this->{"get{$driver}Arguments"}($connection);
}







public function commandEnvironment(array $connection)
{
$driver = ucfirst($connection['driver']);

if (method_exists($this, "get{$driver}Environment")) {
return $this->{"get{$driver}Environment"}($connection);
}

return null;
}







public function getCommand(array $connection)
{
return [
'mysql' => 'mysql',
'mariadb' => 'mysql',
'pgsql' => 'psql',
'sqlite' => 'sqlite3',
'sqlsrv' => 'sqlcmd',
][$connection['driver']];
}







protected function getMysqlArguments(array $connection)
{
return array_merge([
'--host='.$connection['host'],
'--port='.$connection['port'],
'--user='.$connection['username'],
], $this->getOptionalArguments([
'password' => '--password='.$connection['password'],
'unix_socket' => '--socket='.($connection['unix_socket'] ?? ''),
'charset' => '--default-character-set='.($connection['charset'] ?? ''),
], $connection), [$connection['database']]);
}







protected function getMariaDbArguments(array $connection)
{
return $this->getMysqlArguments($connection);
}







protected function getPgsqlArguments(array $connection)
{
return [$connection['database']];
}







protected function getSqliteArguments(array $connection)
{
return [$connection['database']];
}







protected function getSqlsrvArguments(array $connection)
{
return array_merge(...$this->getOptionalArguments([
'database' => ['-d', $connection['database']],
'username' => ['-U', $connection['username']],
'password' => ['-P', $connection['password']],
'host' => ['-S', 'tcp:'.$connection['host']
.($connection['port'] ? ','.$connection['port'] : ''), ],
'trust_server_certificate' => ['-C'],
], $connection));
}







protected function getPgsqlEnvironment(array $connection)
{
return array_merge(...$this->getOptionalArguments([
'username' => ['PGUSER' => $connection['username']],
'host' => ['PGHOST' => $connection['host']],
'port' => ['PGPORT' => $connection['port']],
'password' => ['PGPASSWORD' => $connection['password']],
], $connection));
}








protected function getOptionalArguments(array $args, array $connection)
{
return array_values(array_filter($args, function ($key) use ($connection) {
return ! empty($connection[$key]);
}, ARRAY_FILTER_USE_KEY));
}
}
