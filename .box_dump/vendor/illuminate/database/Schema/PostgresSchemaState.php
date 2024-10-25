<?php

namespace Illuminate\Database\Schema;

use Illuminate\Database\Connection;

class PostgresSchemaState extends SchemaState
{







public function dump(Connection $connection, $path)
{
$commands = collect([
$this->baseDumpCommand().' --schema-only > '.$path,
]);

if ($this->hasMigrationTable()) {
$commands->push($this->baseDumpCommand().' -t '.$this->getMigrationTable().' --data-only >> '.$path);
}

$commands->map(function ($command, $path) {
$this->makeProcess($command)->mustRun($this->output, array_merge($this->baseVariables($this->connection->getConfig()), [
'LARAVEL_LOAD_PATH' => $path,
]));
});
}







public function load($path)
{
$command = 'pg_restore --no-owner --no-acl --clean --if-exists --host="${:LARAVEL_LOAD_HOST}" --port="${:LARAVEL_LOAD_PORT}" --username="${:LARAVEL_LOAD_USER}" --dbname="${:LARAVEL_LOAD_DATABASE}" "${:LARAVEL_LOAD_PATH}"';

if (str_ends_with($path, '.sql')) {
$command = 'psql --file="${:LARAVEL_LOAD_PATH}" --host="${:LARAVEL_LOAD_HOST}" --port="${:LARAVEL_LOAD_PORT}" --username="${:LARAVEL_LOAD_USER}" --dbname="${:LARAVEL_LOAD_DATABASE}"';
}

$process = $this->makeProcess($command);

$process->mustRun(null, array_merge($this->baseVariables($this->connection->getConfig()), [
'LARAVEL_LOAD_PATH' => $path,
]));
}






protected function getMigrationTable(): string
{
[$schema, $table] = $this->connection->getSchemaBuilder()->parseSchemaAndTable($this->migrationTable);

return $schema.'.'.$this->connection->getTablePrefix().$table;
}






protected function baseDumpCommand()
{
return 'pg_dump --no-owner --no-acl --host="${:LARAVEL_LOAD_HOST}" --port="${:LARAVEL_LOAD_PORT}" --username="${:LARAVEL_LOAD_USER}" --dbname="${:LARAVEL_LOAD_DATABASE}"';
}







protected function baseVariables(array $config)
{
$config['host'] ??= '';

return [
'LARAVEL_LOAD_HOST' => is_array($config['host']) ? $config['host'][0] : $config['host'],
'LARAVEL_LOAD_PORT' => $config['port'] ?? '',
'LARAVEL_LOAD_USER' => $config['username'],
'PGPASSWORD' => $config['password'],
'LARAVEL_LOAD_DATABASE' => $config['database'],
];
}
}
