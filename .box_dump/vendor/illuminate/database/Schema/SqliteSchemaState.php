<?php

namespace Illuminate\Database\Schema;

use Illuminate\Database\Connection;

class SqliteSchemaState extends SchemaState
{







public function dump(Connection $connection, $path)
{
with($process = $this->makeProcess(
$this->baseCommand().' ".schema --indent"'
))->setTimeout(null)->mustRun(null, array_merge($this->baseVariables($this->connection->getConfig()), [

]));

$migrations = preg_replace('/CREATE TABLE sqlite_.+?\);[\r\n]+/is', '', $process->getOutput());

$this->files->put($path, $migrations.PHP_EOL);

if ($this->hasMigrationTable()) {
$this->appendMigrationData($path);
}
}







protected function appendMigrationData(string $path)
{
with($process = $this->makeProcess(
$this->baseCommand().' ".dump \''.$this->getMigrationTable().'\'"'
))->mustRun(null, array_merge($this->baseVariables($this->connection->getConfig()), [

]));

$migrations = collect(preg_split("/\r\n|\n|\r/", $process->getOutput()))->filter(function ($line) {
return preg_match('/^\s*(--|INSERT\s)/iu', $line) === 1 &&
strlen($line) > 0;
})->all();

$this->files->append($path, implode(PHP_EOL, $migrations).PHP_EOL);
}







public function load($path)
{
if ($this->connection->getDatabaseName() === ':memory:') {
$this->connection->getPdo()->exec($this->files->get($path));

return;
}

$process = $this->makeProcess($this->baseCommand().' < "${:LARAVEL_LOAD_PATH}"');

$process->mustRun(null, array_merge($this->baseVariables($this->connection->getConfig()), [
'LARAVEL_LOAD_PATH' => $path,
]));
}






protected function baseCommand()
{
return 'sqlite3 "${:LARAVEL_LOAD_DATABASE}"';
}







protected function baseVariables(array $config)
{
return [
'LARAVEL_LOAD_DATABASE' => $config['database'],
];
}
}
