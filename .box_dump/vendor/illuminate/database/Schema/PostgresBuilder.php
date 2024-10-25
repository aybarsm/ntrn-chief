<?php

namespace Illuminate\Database\Schema;

use Illuminate\Database\Concerns\ParsesSearchPath;
use InvalidArgumentException;

class PostgresBuilder extends Builder
{
use ParsesSearchPath {
parseSearchPath as baseParseSearchPath;
}







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

$view = $this->connection->getTablePrefix().$view;

foreach ($this->getViews() as $value) {
if (strtolower($view) === strtolower($value['name'])
&& strtolower($schema) === strtolower($value['schema'])) {
return true;
}
}

return false;
}






public function getTypes()
{
return $this->connection->getPostProcessor()->processTypes(
$this->connection->selectFromWriteConnection($this->grammar->compileTypes())
);
}






public function dropAllTables()
{
$tables = [];

$excludedTables = $this->grammar->escapeNames(
$this->connection->getConfig('dont_drop') ?? ['spatial_ref_sys']
);

$schemas = $this->grammar->escapeNames($this->getSchemas());

foreach ($this->getTables() as $table) {
$qualifiedName = $table['schema'].'.'.$table['name'];

if (empty(array_intersect($this->grammar->escapeNames([$table['name'], $qualifiedName]), $excludedTables))
&& in_array($this->grammar->escapeNames([$table['schema']])[0], $schemas)) {
$tables[] = $qualifiedName;
}
}

if (empty($tables)) {
return;
}

$this->connection->statement(
$this->grammar->compileDropAllTables($tables)
);
}






public function dropAllViews()
{
$views = [];

$schemas = $this->grammar->escapeNames($this->getSchemas());

foreach ($this->getViews() as $view) {
if (in_array($this->grammar->escapeNames([$view['schema']])[0], $schemas)) {
$views[] = $view['schema'].'.'.$view['name'];
}
}

if (empty($views)) {
return;
}

$this->connection->statement(
$this->grammar->compileDropAllViews($views)
);
}






public function dropAllTypes()
{
$types = [];
$domains = [];

$schemas = $this->grammar->escapeNames($this->getSchemas());

foreach ($this->getTypes() as $type) {
if (! $type['implicit'] && in_array($this->grammar->escapeNames([$type['schema']])[0], $schemas)) {
if ($type['type'] === 'domain') {
$domains[] = $type['schema'].'.'.$type['name'];
} else {
$types[] = $type['schema'].'.'.$type['name'];
}
}
}

if (! empty($types)) {
$this->connection->statement($this->grammar->compileDropAllTypes($types));
}

if (! empty($domains)) {
$this->connection->statement($this->grammar->compileDropAllDomains($domains));
}
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






protected function getSchemas()
{
return $this->parseSearchPath(
$this->connection->getConfig('search_path') ?: $this->connection->getConfig('schema') ?: 'public'
);
}







public function parseSchemaAndTable($reference)
{
$parts = explode('.', $reference);

if (count($parts) > 2) {
$database = $parts[0];

throw new InvalidArgumentException("Using three-part reference is not supported, you may use `Schema::connection('$database')` instead.");
}




$schema = $this->getSchemas()[0];

if (count($parts) === 2) {
$schema = $parts[0];
array_shift($parts);
}

return [$schema, $parts[0]];
}







protected function parseSearchPath($searchPath)
{
return array_map(function ($schema) {
return $schema === '$user'
? $this->connection->getConfig('username')
: $schema;
}, $this->baseParseSearchPath($searchPath));
}
}
