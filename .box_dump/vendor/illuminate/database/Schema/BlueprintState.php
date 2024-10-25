<?php

namespace Illuminate\Database\Schema;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class BlueprintState
{





protected $blueprint;






protected $connection;






protected $grammar;






private $columns;






private $primaryKey;






private $indexes;






private $foreignKeys;









public function __construct(Blueprint $blueprint, Connection $connection, Grammar $grammar)
{
$this->blueprint = $blueprint;
$this->connection = $connection;
$this->grammar = $grammar;

$schema = $connection->getSchemaBuilder();
$table = $blueprint->getTable();

$this->columns = collect($schema->getColumns($table))->map(fn ($column) => new ColumnDefinition([
'name' => $column['name'],
'type' => $column['type_name'],
'full_type_definition' => $column['type'],
'nullable' => $column['nullable'],
'default' => is_null($column['default']) ? null : new Expression(Str::wrap($column['default'], '(', ')')),
'autoIncrement' => $column['auto_increment'],
'collation' => $column['collation'],
'comment' => $column['comment'],
'virtualAs' => ! is_null($column['generation']) && $column['generation']['type'] === 'virtual'
? $column['generation']['expression'] : null,
'storedAs' => ! is_null($column['generation']) && $column['generation']['type'] === 'stored'
? $column['generation']['expression'] : null,
]))->all();

[$primary, $indexes] = collect($schema->getIndexes($table))->map(fn ($index) => new IndexDefinition([
'name' => match (true) {
$index['primary'] => 'primary',
$index['unique'] => 'unique',
default => 'index',
},
'index' => $index['name'],
'columns' => $index['columns'],
]))->partition(fn ($index) => $index->name === 'primary');

$this->indexes = $indexes->all();
$this->primaryKey = $primary->first();

$this->foreignKeys = collect($schema->getForeignKeys($table))->map(fn ($foreignKey) => new ForeignKeyDefinition([
'columns' => $foreignKey['columns'],
'on' => new Expression($foreignKey['foreign_table']),
'references' => $foreignKey['foreign_columns'],
'onUpdate' => $foreignKey['on_update'],
'onDelete' => $foreignKey['on_delete'],
]))->all();
}






public function getPrimaryKey()
{
return $this->primaryKey;
}






public function getColumns()
{
return $this->columns;
}






public function getIndexes()
{
return $this->indexes;
}






public function getForeignKeys()
{
return $this->foreignKeys;
}







public function update(Fluent $command)
{
switch ($command->name) {
case 'alter':

break;

case 'add':
$this->columns[] = $command->column;
break;

case 'change':
foreach ($this->columns as &$column) {
if ($column->name === $command->column->name) {
$column = $command->column;
break;
}
}

break;

case 'renameColumn':
foreach ($this->columns as $column) {
if ($column->name === $command->from) {
$column->name = $command->to;
break;
}
}

if ($this->primaryKey) {
$this->primaryKey->columns = str_replace($command->from, $command->to, $this->primaryKey->columns);
}

foreach ($this->indexes as $index) {
$index->columns = str_replace($command->from, $command->to, $index->columns);
}

foreach ($this->foreignKeys as $foreignKey) {
$foreignKey->columns = str_replace($command->from, $command->to, $foreignKey->columns);
}

break;

case 'dropColumn':
$this->columns = array_values(
array_filter($this->columns, fn ($column) => ! in_array($column->name, $command->columns))
);

break;

case 'primary':
$this->primaryKey = $command;
break;

case 'unique':
case 'index':
$this->indexes[] = $command;
break;

case 'renameIndex':
foreach ($this->indexes as $index) {
if ($index->index === $command->from) {
$index->index = $command->to;
break;
}
}

break;

case 'foreign':
$this->foreignKeys[] = $command;
break;

case 'dropPrimary':
$this->primaryKey = null;
break;

case 'dropIndex':
case 'dropUnique':
$this->indexes = array_values(
array_filter($this->indexes, fn ($index) => $index->index !== $command->index)
);

break;

case 'dropForeign':
$this->foreignKeys = array_values(
array_filter($this->foreignKeys, fn ($fk) => $fk->columns !== $command->columns)
);

break;
}
}
}
