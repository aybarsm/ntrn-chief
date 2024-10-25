<?php

namespace Illuminate\Database\Schema;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use LogicException;

class Builder
{
use Macroable;






protected $connection;






protected $grammar;






protected $resolver;






public static $defaultStringLength = 255;






public static $defaultMorphKeyType = 'int';







public function __construct(Connection $connection)
{
$this->connection = $connection;
$this->grammar = $connection->getSchemaGrammar();
}







public static function defaultStringLength($length)
{
static::$defaultStringLength = $length;
}









public static function defaultMorphKeyType(string $type)
{
if (! in_array($type, ['int', 'uuid', 'ulid'])) {
throw new InvalidArgumentException("Morph key type must be 'int', 'uuid', or 'ulid'.");
}

static::$defaultMorphKeyType = $type;
}






public static function morphUsingUuids()
{
static::defaultMorphKeyType('uuid');
}






public static function morphUsingUlids()
{
static::defaultMorphKeyType('ulid');
}









public function createDatabase($name)
{
throw new LogicException('This database driver does not support creating databases.');
}









public function dropDatabaseIfExists($name)
{
throw new LogicException('This database driver does not support dropping databases.');
}







public function hasTable($table)
{
$table = $this->connection->getTablePrefix().$table;

foreach ($this->getTables() as $value) {
if (strtolower($table) === strtolower($value['name'])) {
return true;
}
}

return false;
}







public function hasView($view)
{
$view = $this->connection->getTablePrefix().$view;

foreach ($this->getViews() as $value) {
if (strtolower($view) === strtolower($value['name'])) {
return true;
}
}

return false;
}






public function getTables()
{
return $this->connection->getPostProcessor()->processTables(
$this->connection->selectFromWriteConnection($this->grammar->compileTables())
);
}






public function getTableListing()
{
return array_column($this->getTables(), 'name');
}






public function getViews()
{
return $this->connection->getPostProcessor()->processViews(
$this->connection->selectFromWriteConnection($this->grammar->compileViews())
);
}






public function getTypes()
{
throw new LogicException('This database driver does not support user-defined types.');
}








public function hasColumn($table, $column)
{
return in_array(
strtolower($column), array_map('strtolower', $this->getColumnListing($table))
);
}








public function hasColumns($table, array $columns)
{
$tableColumns = array_map('strtolower', $this->getColumnListing($table));

foreach ($columns as $column) {
if (! in_array(strtolower($column), $tableColumns)) {
return false;
}
}

return true;
}









public function whenTableHasColumn(string $table, string $column, Closure $callback)
{
if ($this->hasColumn($table, $column)) {
$this->table($table, fn (Blueprint $table) => $callback($table));
}
}









public function whenTableDoesntHaveColumn(string $table, string $column, Closure $callback)
{
if (! $this->hasColumn($table, $column)) {
$this->table($table, fn (Blueprint $table) => $callback($table));
}
}









public function getColumnType($table, $column, $fullDefinition = false)
{
$columns = $this->getColumns($table);

foreach ($columns as $value) {
if (strtolower($value['name']) === strtolower($column)) {
return $fullDefinition ? $value['type'] : $value['type_name'];
}
}

throw new InvalidArgumentException("There is no column with name '$column' on table '$table'.");
}







public function getColumnListing($table)
{
return array_column($this->getColumns($table), 'name');
}







public function getColumns($table)
{
$table = $this->connection->getTablePrefix().$table;

return $this->connection->getPostProcessor()->processColumns(
$this->connection->selectFromWriteConnection($this->grammar->compileColumns($table))
);
}







public function getIndexes($table)
{
$table = $this->connection->getTablePrefix().$table;

return $this->connection->getPostProcessor()->processIndexes(
$this->connection->selectFromWriteConnection($this->grammar->compileIndexes($table))
);
}







public function getIndexListing($table)
{
return array_column($this->getIndexes($table), 'name');
}









public function hasIndex($table, $index, $type = null)
{
$type = is_null($type) ? $type : strtolower($type);

foreach ($this->getIndexes($table) as $value) {
$typeMatches = is_null($type)
|| ($type === 'primary' && $value['primary'])
|| ($type === 'unique' && $value['unique'])
|| $type === $value['type'];

if (($value['name'] === $index || $value['columns'] === $index) && $typeMatches) {
return true;
}
}

return false;
}







public function getForeignKeys($table)
{
$table = $this->connection->getTablePrefix().$table;

return $this->connection->getPostProcessor()->processForeignKeys(
$this->connection->selectFromWriteConnection($this->grammar->compileForeignKeys($table))
);
}








public function table($table, Closure $callback)
{
$this->build($this->createBlueprint($table, $callback));
}








public function create($table, Closure $callback)
{
$this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback) {
$blueprint->create();

$callback($blueprint);
}));
}







public function drop($table)
{
$this->build(tap($this->createBlueprint($table), function ($blueprint) {
$blueprint->drop();
}));
}







public function dropIfExists($table)
{
$this->build(tap($this->createBlueprint($table), function ($blueprint) {
$blueprint->dropIfExists();
}));
}








public function dropColumns($table, $columns)
{
$this->table($table, function (Blueprint $blueprint) use ($columns) {
$blueprint->dropColumn($columns);
});
}








public function dropAllTables()
{
throw new LogicException('This database driver does not support dropping all tables.');
}








public function dropAllViews()
{
throw new LogicException('This database driver does not support dropping all views.');
}








public function dropAllTypes()
{
throw new LogicException('This database driver does not support dropping all types.');
}








public function rename($from, $to)
{
$this->build(tap($this->createBlueprint($from), function ($blueprint) use ($to) {
$blueprint->rename($to);
}));
}






public function enableForeignKeyConstraints()
{
return $this->connection->statement(
$this->grammar->compileEnableForeignKeyConstraints()
);
}






public function disableForeignKeyConstraints()
{
return $this->connection->statement(
$this->grammar->compileDisableForeignKeyConstraints()
);
}







public function withoutForeignKeyConstraints(Closure $callback)
{
$this->disableForeignKeyConstraints();

try {
return $callback();
} finally {
$this->enableForeignKeyConstraints();
}
}







protected function build(Blueprint $blueprint)
{
$blueprint->build($this->connection, $this->grammar);
}








protected function createBlueprint($table, ?Closure $callback = null)
{
$prefix = $this->connection->getConfig('prefix_indexes')
? $this->connection->getConfig('prefix')
: '';

if (isset($this->resolver)) {
return call_user_func($this->resolver, $table, $callback, $prefix);
}

return Container::getInstance()->make(Blueprint::class, compact('table', 'callback', 'prefix'));
}






public function getConnection()
{
return $this->connection;
}







public function setConnection(Connection $connection)
{
$this->connection = $connection;

return $this;
}







public function blueprintResolver(Closure $resolver)
{
$this->resolver = $resolver;
}
}
