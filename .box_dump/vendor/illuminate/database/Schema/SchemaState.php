<?php

namespace Illuminate\Database\Schema;

use Illuminate\Database\Connection;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

abstract class SchemaState
{





protected $connection;






protected $files;






protected $migrationTable = 'migrations';






protected $processFactory;






protected $output;









public function __construct(Connection $connection, ?Filesystem $files = null, ?callable $processFactory = null)
{
$this->connection = $connection;

$this->files = $files ?: new Filesystem;

$this->processFactory = $processFactory ?: function (...$arguments) {
return Process::fromShellCommandline(...$arguments)->setTimeout(null);
};

$this->handleOutputUsing(function () {

});
}








abstract public function dump(Connection $connection, $path);







abstract public function load($path);







public function makeProcess(...$arguments)
{
return call_user_func($this->processFactory, ...$arguments);
}






public function hasMigrationTable(): bool
{
return $this->connection->getSchemaBuilder()->hasTable($this->migrationTable);
}






protected function getMigrationTable(): string
{
return $this->connection->getTablePrefix().$this->migrationTable;
}







public function withMigrationTable(string $table)
{
$this->migrationTable = $table;

return $this;
}







public function handleOutputUsing(callable $output)
{
$this->output = $output;

return $this;
}
}
