<?php

namespace Illuminate\Database\Schema\Grammars;

use BackedEnum;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Concerns\CompilesJsonPaths;
use Illuminate\Database\Connection;
use Illuminate\Database\Grammar as BaseGrammar;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use LogicException;
use RuntimeException;

abstract class Grammar extends BaseGrammar
{
use CompilesJsonPaths;






protected $modifiers = [];






protected $transactions = false;






protected $fluentCommands = [];










public function compileCreateDatabase($name, $connection)
{
throw new LogicException('This database driver does not support creating databases.');
}









public function compileDropDatabaseIfExists($name)
{
throw new LogicException('This database driver does not support dropping databases.');
}









public function compileRenameColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
{
return sprintf('alter table %s rename column %s to %s',
$this->wrapTable($blueprint),
$this->wrap($command->from),
$this->wrap($command->to)
);
}











public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection)
{
throw new LogicException('This database driver does not support modifying columns.');
}










public function compileFulltext(Blueprint $blueprint, Fluent $command)
{
throw new RuntimeException('This database driver does not support fulltext index creation.');
}










public function compileDropFullText(Blueprint $blueprint, Fluent $command)
{
throw new RuntimeException('This database driver does not support fulltext index removal.');
}








public function compileForeign(Blueprint $blueprint, Fluent $command)
{



$sql = sprintf('alter table %s add constraint %s ',
$this->wrapTable($blueprint),
$this->wrap($command->index)
);




$sql .= sprintf('foreign key (%s) references %s (%s)',
$this->columnize($command->columns),
$this->wrapTable($command->on),
$this->columnize((array) $command->references)
);




if (! is_null($command->onDelete)) {
$sql .= " on delete {$command->onDelete}";
}

if (! is_null($command->onUpdate)) {
$sql .= " on update {$command->onUpdate}";
}

return $sql;
}








public function compileDropForeign(Blueprint $blueprint, Fluent $command)
{
throw new RuntimeException('This database driver does not support dropping foreign keys.');
}







protected function getColumns(Blueprint $blueprint)
{
$columns = [];

foreach ($blueprint->getAddedColumns() as $column) {
$columns[] = $this->getColumn($blueprint, $column);
}

return $columns;
}








protected function getColumn(Blueprint $blueprint, $column)
{



$sql = $this->wrap($column).' '.$this->getType($column);

return $this->addModifiers($sql, $blueprint, $column);
}







protected function getType(Fluent $column)
{
return $this->{'type'.ucfirst($column->type)}($column);
}









protected function typeComputed(Fluent $column)
{
throw new RuntimeException('This database driver does not support the computed type.');
}









protected function typeVector(Fluent $column)
{
throw new RuntimeException('This database driver does not support the vector type.');
}









protected function addModifiers($sql, Blueprint $blueprint, Fluent $column)
{
foreach ($this->modifiers as $modifier) {
if (method_exists($this, $method = "modify{$modifier}")) {
$sql .= $this->{$method}($blueprint, $column);
}
}

return $sql;
}








protected function getCommandByName(Blueprint $blueprint, $name)
{
$commands = $this->getCommandsByName($blueprint, $name);

if (count($commands) > 0) {
return reset($commands);
}
}








protected function getCommandsByName(Blueprint $blueprint, $name)
{
return array_filter($blueprint->getCommands(), function ($value) use ($name) {
return $value->name == $name;
});
}








protected function hasCommand(Blueprint $blueprint, $name)
{
foreach ($blueprint->getCommands() as $command) {
if ($command->name === $name) {
return true;
}
}

return false;
}








public function prefixArray($prefix, array $values)
{
return array_map(function ($value) use ($prefix) {
return $prefix.' '.$value;
}, $values);
}







public function wrapTable($table)
{
return parent::wrapTable(
$table instanceof Blueprint ? $table->getTable() : $table
);
}







public function wrap($value)
{
return parent::wrap(
$value instanceof Fluent ? $value->name : $value,
);
}







protected function getDefaultValue($value)
{
if ($value instanceof Expression) {
return $this->getValue($value);
}

if ($value instanceof BackedEnum) {
return "'{$value->value}'";
}

return is_bool($value)
? "'".(int) $value."'"
: "'".(string) $value."'";
}






public function getFluentCommands()
{
return $this->fluentCommands;
}






public function supportsSchemaTransactions()
{
return $this->transactions;
}
}
