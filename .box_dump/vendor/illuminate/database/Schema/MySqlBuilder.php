<?php

namespace Illuminate\Database\Schema;

class MySqlBuilder extends Builder
{






public function createDatabase($name)
{
return $this->connection->statement(
$this->grammar->compileCreateDatabase($name, $this->connection)
);
}







public function dropDatabaseIfExists($name)
{
return $this->connection->statement(
$this->grammar->compileDropDatabaseIfExists($name)
);
}







public function hasTable($table)
{
$table = $this->connection->getTablePrefix().$table;

$database = $this->connection->getDatabaseName();

return (bool) $this->connection->scalar(
$this->grammar->compileTableExists($database, $table)
);
}






public function getTables()
{
return $this->connection->getPostProcessor()->processTables(
$this->connection->selectFromWriteConnection(
$this->grammar->compileTables($this->connection->getDatabaseName())
)
);
}






public function getViews()
{
return $this->connection->getPostProcessor()->processViews(
$this->connection->selectFromWriteConnection(
$this->grammar->compileViews($this->connection->getDatabaseName())
)
);
}







public function getColumns($table)
{
$table = $this->connection->getTablePrefix().$table;

$results = $this->connection->selectFromWriteConnection(
$this->grammar->compileColumns($this->connection->getDatabaseName(), $table)
);

return $this->connection->getPostProcessor()->processColumns($results);
}







public function getIndexes($table)
{
$table = $this->connection->getTablePrefix().$table;

return $this->connection->getPostProcessor()->processIndexes(
$this->connection->selectFromWriteConnection(
$this->grammar->compileIndexes($this->connection->getDatabaseName(), $table)
)
);
}







public function getForeignKeys($table)
{
$table = $this->connection->getTablePrefix().$table;

return $this->connection->getPostProcessor()->processForeignKeys(
$this->connection->selectFromWriteConnection(
$this->grammar->compileForeignKeys($this->connection->getDatabaseName(), $table)
)
);
}






public function dropAllTables()
{
$tables = array_column($this->getTables(), 'name');

if (empty($tables)) {
return;
}

$this->disableForeignKeyConstraints();

$this->connection->statement(
$this->grammar->compileDropAllTables($tables)
);

$this->enableForeignKeyConstraints();
}






public function dropAllViews()
{
$views = array_column($this->getViews(), 'name');

if (empty($views)) {
return;
}

$this->connection->statement(
$this->grammar->compileDropAllViews($views)
);
}
}
