<?php

namespace Illuminate\Database\Schema;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;

class SQLiteBuilder extends Builder
{






public function createDatabase($name)
{
return File::put($name, '') !== false;
}







public function dropDatabaseIfExists($name)
{
return File::exists($name)
? File::delete($name)
: true;
}







public function hasTable($table)
{
$table = $this->connection->getTablePrefix().$table;

return (bool) $this->connection->scalar(
$this->grammar->compileTableExists($table)
);
}







public function getTables($withSize = true)
{
if ($withSize) {
try {
$withSize = $this->connection->scalar($this->grammar->compileDbstatExists());
} catch (QueryException $e) {
$withSize = false;
}
}

return $this->connection->getPostProcessor()->processTables(
$this->connection->selectFromWriteConnection($this->grammar->compileTables($withSize))
);
}







public function getColumns($table)
{
$table = $this->connection->getTablePrefix().$table;

return $this->connection->getPostProcessor()->processColumns(
$this->connection->selectFromWriteConnection($this->grammar->compileColumns($table)),
$this->connection->scalar($this->grammar->compileSqlCreateStatement($table))
);
}






public function dropAllTables()
{
if ($this->connection->getDatabaseName() !== ':memory:') {
return $this->refreshDatabaseFile();
}

$this->connection->select($this->grammar->compileEnableWriteableSchema());

$this->connection->select($this->grammar->compileDropAllTables());

$this->connection->select($this->grammar->compileDisableWriteableSchema());

$this->connection->select($this->grammar->compileRebuild());
}






public function dropAllViews()
{
$this->connection->select($this->grammar->compileEnableWriteableSchema());

$this->connection->select($this->grammar->compileDropAllViews());

$this->connection->select($this->grammar->compileDisableWriteableSchema());

$this->connection->select($this->grammar->compileRebuild());
}







public function setBusyTimeout($milliseconds)
{
return $this->connection->statement(
$this->grammar->compileSetBusyTimeout($milliseconds)
);
}







public function setJournalMode($mode)
{
return $this->connection->statement(
$this->grammar->compileSetJournalMode($mode)
);
}







public function setSynchronous($mode)
{
return $this->connection->statement(
$this->grammar->compileSetSynchronous($mode)
);
}






public function refreshDatabaseFile()
{
file_put_contents($this->connection->getDatabaseName(), '');
}
}
