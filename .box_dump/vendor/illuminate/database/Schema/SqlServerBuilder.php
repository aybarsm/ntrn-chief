<?php

namespace Illuminate\Database\Schema;

use InvalidArgumentException;

class SqlServerBuilder extends Builder
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
[$schema, $table] = $this->parseSchemaAndTable($table);

$table = $this->connection->getTablePrefix().$table;

return (bool) $this->connection->scalar(
$this->grammar->compileTableExists($schema, $table)
);
}







public function hasView($view)
{
[$schema, $view] = $this->parseSchemaAndTable($view);

$schema ??= $this->getDefaultSchema();
$view = $this->connection->getTablePrefix().$view;

foreach ($this->getViews() as $value) {
if (strtolower($view) === strtolower($value['name'])
&& strtolower($schema) === strtolower($value['schema'])) {
return true;
}
}

return false;
}






public function dropAllTables()
{
$this->connection->statement($this->grammar->compileDropAllForeignKeys());

$this->connection->statement($this->grammar->compileDropAllTables());
}






public function dropAllViews()
{
$this->connection->statement($this->grammar->compileDropAllViews());
}







public function getColumns($table)
{
[$schema, $table] = $this->parseSchemaAndTable($table);

$table = $this->connection->getTablePrefix().$table;

$results = $this->connection->selectFromWriteConnection(
$this->grammar->compileColumns($schema, $table)
);

return $this->connection->getPostProcessor()->processColumns($results);
}







public function getIndexes($table)
{
[$schema, $table] = $this->parseSchemaAndTable($table);

$table = $this->connection->getTablePrefix().$table;

return $this->connection->getPostProcessor()->processIndexes(
$this->connection->selectFromWriteConnection($this->grammar->compileIndexes($schema, $table))
);
}







public function getForeignKeys($table)
{
[$schema, $table] = $this->parseSchemaAndTable($table);

$table = $this->connection->getTablePrefix().$table;

return $this->connection->getPostProcessor()->processForeignKeys(
$this->connection->selectFromWriteConnection($this->grammar->compileForeignKeys($schema, $table))
);
}






protected function getDefaultSchema()
{
return $this->connection->scalar($this->grammar->compileDefaultSchema());
}







protected function parseSchemaAndTable($reference)
{
$parts = array_pad(explode('.', $reference, 2), -2, null);

if (str_contains($parts[1], '.')) {
$database = $parts[0];

throw new InvalidArgumentException("Using three-part reference is not supported, you may use `Schema::connection('$database')` instead.");
}

return $parts;
}
}
