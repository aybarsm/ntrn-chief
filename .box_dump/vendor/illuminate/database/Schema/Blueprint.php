<?php

namespace Illuminate\Database\Schema;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Grammars\Grammar;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Support\Fluent;
use Illuminate\Support\Traits\Macroable;

class Blueprint
{
use Macroable;






protected $table;






protected $prefix;






protected $columns = [];






protected $commands = [];






public $engine;






public $charset;






public $collation;






public $temporary = false;






public $after;






protected $state;









public function __construct($table, ?Closure $callback = null, $prefix = '')
{
$this->table = $table;
$this->prefix = $prefix;

if (! is_null($callback)) {
$callback($this);
}
}








public function build(Connection $connection, Grammar $grammar)
{
foreach ($this->toSql($connection, $grammar) as $statement) {
$connection->statement($statement);
}
}








public function toSql(Connection $connection, Grammar $grammar)
{
$this->addImpliedCommands($connection, $grammar);

$statements = [];




$this->ensureCommandsAreValid($connection);

foreach ($this->commands as $command) {
if ($command->shouldBeSkipped) {
continue;
}

$method = 'compile'.ucfirst($command->name);

if (method_exists($grammar, $method) || $grammar::hasMacro($method)) {
if ($this->hasState()) {
$this->state->update($command);
}

if (! is_null($sql = $grammar->$method($this, $command, $connection))) {
$statements = array_merge($statements, (array) $sql);
}
}
}

return $statements;
}









protected function ensureCommandsAreValid(Connection $connection)
{

}









protected function commandsNamed(array $names)
{
return collect($this->commands)->filter(function ($command) use ($names) {
return in_array($command->name, $names);
});
}








protected function addImpliedCommands(Connection $connection, Grammar $grammar)
{
$this->addFluentIndexes($connection, $grammar);

$this->addFluentCommands($connection, $grammar);

if (! $this->creating()) {
$this->commands = array_map(
fn ($command) => $command instanceof ColumnDefinition
? $this->createCommand($command->change ? 'change' : 'add', ['column' => $command])
: $command,
$this->commands
);

$this->addAlterCommands($connection, $grammar);
}
}








protected function addFluentIndexes(Connection $connection, Grammar $grammar)
{
foreach ($this->columns as $column) {
foreach (['primary', 'unique', 'index', 'fulltext', 'fullText', 'spatialIndex'] as $index) {



if ($index === 'primary' && $column->autoIncrement && $column->change && $grammar instanceof MySqlGrammar) {
continue 2;
}




if ($column->{$index} === true) {
$this->{$index}($column->name);
$column->{$index} = null;

continue 2;
}




elseif ($column->{$index} === false && $column->change) {
$this->{'drop'.ucfirst($index)}([$column->name]);
$column->{$index} = null;

continue 2;
}




elseif (isset($column->{$index})) {
$this->{$index}($column->name, $column->{$index});
$column->{$index} = null;

continue 2;
}
}
}
}








public function addFluentCommands(Connection $connection, Grammar $grammar)
{
foreach ($this->columns as $column) {
foreach ($grammar->getFluentCommands() as $commandName) {
$this->addCommand($commandName, compact('column'));
}
}
}








public function addAlterCommands(Connection $connection, Grammar $grammar)
{
if (! $grammar instanceof SQLiteGrammar) {
return;
}

$alterCommands = $grammar->getAlterCommands($connection);

[$commands, $lastCommandWasAlter, $hasAlterCommand] = [
[], false, false,
];

foreach ($this->commands as $command) {
if (in_array($command->name, $alterCommands)) {
$hasAlterCommand = true;
$lastCommandWasAlter = true;
} elseif ($lastCommandWasAlter) {
$commands[] = $this->createCommand('alter');
$lastCommandWasAlter = false;
}

$commands[] = $command;
}

if ($lastCommandWasAlter) {
$commands[] = $this->createCommand('alter');
}

if ($hasAlterCommand) {
$this->state = new BlueprintState($this, $connection, $grammar);
}

$this->commands = $commands;
}






public function creating()
{
return collect($this->commands)->contains(function ($command) {
return ! $command instanceof ColumnDefinition && $command->name === 'create';
});
}






public function create()
{
return $this->addCommand('create');
}







public function engine($engine)
{
$this->engine = $engine;
}






public function innoDb()
{
$this->engine('InnoDB');
}







public function charset($charset)
{
$this->charset = $charset;
}







public function collation($collation)
{
$this->collation = $collation;
}






public function temporary()
{
$this->temporary = true;
}






public function drop()
{
return $this->addCommand('drop');
}






public function dropIfExists()
{
return $this->addCommand('dropIfExists');
}







public function dropColumn($columns)
{
$columns = is_array($columns) ? $columns : func_get_args();

return $this->addCommand('dropColumn', compact('columns'));
}








public function renameColumn($from, $to)
{
return $this->addCommand('renameColumn', compact('from', 'to'));
}







public function dropPrimary($index = null)
{
return $this->dropIndexCommand('dropPrimary', 'primary', $index);
}







public function dropUnique($index)
{
return $this->dropIndexCommand('dropUnique', 'unique', $index);
}







public function dropIndex($index)
{
return $this->dropIndexCommand('dropIndex', 'index', $index);
}







public function dropFullText($index)
{
return $this->dropIndexCommand('dropFullText', 'fulltext', $index);
}







public function dropSpatialIndex($index)
{
return $this->dropIndexCommand('dropSpatialIndex', 'spatialIndex', $index);
}







public function dropForeign($index)
{
return $this->dropIndexCommand('dropForeign', 'foreign', $index);
}







public function dropConstrainedForeignId($column)
{
$this->dropForeign([$column]);

return $this->dropColumn($column);
}








public function dropForeignIdFor($model, $column = null)
{
if (is_string($model)) {
$model = new $model;
}

return $this->dropForeign([$column ?: $model->getForeignKey()]);
}








public function dropConstrainedForeignIdFor($model, $column = null)
{
if (is_string($model)) {
$model = new $model;
}

return $this->dropConstrainedForeignId($column ?: $model->getForeignKey());
}








public function renameIndex($from, $to)
{
return $this->addCommand('renameIndex', compact('from', 'to'));
}






public function dropTimestamps()
{
$this->dropColumn('created_at', 'updated_at');
}






public function dropTimestampsTz()
{
$this->dropTimestamps();
}







public function dropSoftDeletes($column = 'deleted_at')
{
$this->dropColumn($column);
}







public function dropSoftDeletesTz($column = 'deleted_at')
{
$this->dropSoftDeletes($column);
}






public function dropRememberToken()
{
$this->dropColumn('remember_token');
}








public function dropMorphs($name, $indexName = null)
{
$this->dropIndex($indexName ?: $this->createIndexName('index', ["{$name}_type", "{$name}_id"]));

$this->dropColumn("{$name}_type", "{$name}_id");
}







public function rename($to)
{
return $this->addCommand('rename', compact('to'));
}









public function primary($columns, $name = null, $algorithm = null)
{
return $this->indexCommand('primary', $columns, $name, $algorithm);
}









public function unique($columns, $name = null, $algorithm = null)
{
return $this->indexCommand('unique', $columns, $name, $algorithm);
}









public function index($columns, $name = null, $algorithm = null)
{
return $this->indexCommand('index', $columns, $name, $algorithm);
}









public function fullText($columns, $name = null, $algorithm = null)
{
return $this->indexCommand('fulltext', $columns, $name, $algorithm);
}








public function spatialIndex($columns, $name = null)
{
return $this->indexCommand('spatialIndex', $columns, $name);
}








public function rawIndex($expression, $name)
{
return $this->index([new Expression($expression)], $name);
}








public function foreign($columns, $name = null)
{
$command = new ForeignKeyDefinition(
$this->indexCommand('foreign', $columns, $name)->getAttributes()
);

$this->commands[count($this->commands) - 1] = $command;

return $command;
}







public function id($column = 'id')
{
return $this->bigIncrements($column);
}







public function increments($column)
{
return $this->unsignedInteger($column, true);
}







public function integerIncrements($column)
{
return $this->unsignedInteger($column, true);
}







public function tinyIncrements($column)
{
return $this->unsignedTinyInteger($column, true);
}







public function smallIncrements($column)
{
return $this->unsignedSmallInteger($column, true);
}







public function mediumIncrements($column)
{
return $this->unsignedMediumInteger($column, true);
}







public function bigIncrements($column)
{
return $this->unsignedBigInteger($column, true);
}








public function char($column, $length = null)
{
$length = ! is_null($length) ? $length : Builder::$defaultStringLength;

return $this->addColumn('char', $column, compact('length'));
}








public function string($column, $length = null)
{
$length = $length ?: Builder::$defaultStringLength;

return $this->addColumn('string', $column, compact('length'));
}







public function tinyText($column)
{
return $this->addColumn('tinyText', $column);
}







public function text($column)
{
return $this->addColumn('text', $column);
}







public function mediumText($column)
{
return $this->addColumn('mediumText', $column);
}







public function longText($column)
{
return $this->addColumn('longText', $column);
}









public function integer($column, $autoIncrement = false, $unsigned = false)
{
return $this->addColumn('integer', $column, compact('autoIncrement', 'unsigned'));
}









public function tinyInteger($column, $autoIncrement = false, $unsigned = false)
{
return $this->addColumn('tinyInteger', $column, compact('autoIncrement', 'unsigned'));
}









public function smallInteger($column, $autoIncrement = false, $unsigned = false)
{
return $this->addColumn('smallInteger', $column, compact('autoIncrement', 'unsigned'));
}









public function mediumInteger($column, $autoIncrement = false, $unsigned = false)
{
return $this->addColumn('mediumInteger', $column, compact('autoIncrement', 'unsigned'));
}









public function bigInteger($column, $autoIncrement = false, $unsigned = false)
{
return $this->addColumn('bigInteger', $column, compact('autoIncrement', 'unsigned'));
}








public function unsignedInteger($column, $autoIncrement = false)
{
return $this->integer($column, $autoIncrement, true);
}








public function unsignedTinyInteger($column, $autoIncrement = false)
{
return $this->tinyInteger($column, $autoIncrement, true);
}








public function unsignedSmallInteger($column, $autoIncrement = false)
{
return $this->smallInteger($column, $autoIncrement, true);
}








public function unsignedMediumInteger($column, $autoIncrement = false)
{
return $this->mediumInteger($column, $autoIncrement, true);
}








public function unsignedBigInteger($column, $autoIncrement = false)
{
return $this->bigInteger($column, $autoIncrement, true);
}







public function foreignId($column)
{
return $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
'type' => 'bigInteger',
'name' => $column,
'autoIncrement' => false,
'unsigned' => true,
]));
}








public function foreignIdFor($model, $column = null)
{
if (is_string($model)) {
$model = new $model;
}

$column = $column ?: $model->getForeignKey();

if ($model->getKeyType() === 'int' && $model->getIncrementing()) {
return $this->foreignId($column)->table($model->getTable());
}

$modelTraits = class_uses_recursive($model);

if (in_array(HasUlids::class, $modelTraits, true)) {
return $this->foreignUlid($column, 26)->table($model->getTable());
}

return $this->foreignUuid($column)->table($model->getTable());
}








public function float($column, $precision = 53)
{
return $this->addColumn('float', $column, compact('precision'));
}







public function double($column)
{
return $this->addColumn('double', $column);
}









public function decimal($column, $total = 8, $places = 2)
{
return $this->addColumn('decimal', $column, compact('total', 'places'));
}







public function boolean($column)
{
return $this->addColumn('boolean', $column);
}








public function enum($column, array $allowed)
{
return $this->addColumn('enum', $column, compact('allowed'));
}








public function set($column, array $allowed)
{
return $this->addColumn('set', $column, compact('allowed'));
}







public function json($column)
{
return $this->addColumn('json', $column);
}







public function jsonb($column)
{
return $this->addColumn('jsonb', $column);
}







public function date($column)
{
return $this->addColumn('date', $column);
}








public function dateTime($column, $precision = 0)
{
return $this->addColumn('dateTime', $column, compact('precision'));
}








public function dateTimeTz($column, $precision = 0)
{
return $this->addColumn('dateTimeTz', $column, compact('precision'));
}








public function time($column, $precision = 0)
{
return $this->addColumn('time', $column, compact('precision'));
}








public function timeTz($column, $precision = 0)
{
return $this->addColumn('timeTz', $column, compact('precision'));
}








public function timestamp($column, $precision = 0)
{
return $this->addColumn('timestamp', $column, compact('precision'));
}








public function timestampTz($column, $precision = 0)
{
return $this->addColumn('timestampTz', $column, compact('precision'));
}







public function timestamps($precision = 0)
{
$this->timestamp('created_at', $precision)->nullable();

$this->timestamp('updated_at', $precision)->nullable();
}









public function nullableTimestamps($precision = 0)
{
$this->timestamps($precision);
}







public function timestampsTz($precision = 0)
{
$this->timestampTz('created_at', $precision)->nullable();

$this->timestampTz('updated_at', $precision)->nullable();
}







public function datetimes($precision = 0)
{
$this->datetime('created_at', $precision)->nullable();

$this->datetime('updated_at', $precision)->nullable();
}








public function softDeletes($column = 'deleted_at', $precision = 0)
{
return $this->timestamp($column, $precision)->nullable();
}








public function softDeletesTz($column = 'deleted_at', $precision = 0)
{
return $this->timestampTz($column, $precision)->nullable();
}








public function softDeletesDatetime($column = 'deleted_at', $precision = 0)
{
return $this->datetime($column, $precision)->nullable();
}







public function year($column)
{
return $this->addColumn('year', $column);
}









public function binary($column, $length = null, $fixed = false)
{
return $this->addColumn('binary', $column, compact('length', 'fixed'));
}







public function uuid($column = 'uuid')
{
return $this->addColumn('uuid', $column);
}







public function foreignUuid($column)
{
return $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
'type' => 'uuid',
'name' => $column,
]));
}








public function ulid($column = 'ulid', $length = 26)
{
return $this->char($column, $length);
}








public function foreignUlid($column, $length = 26)
{
return $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
'type' => 'char',
'name' => $column,
'length' => $length,
]));
}







public function ipAddress($column = 'ip_address')
{
return $this->addColumn('ipAddress', $column);
}







public function macAddress($column = 'mac_address')
{
return $this->addColumn('macAddress', $column);
}









public function geometry($column, $subtype = null, $srid = 0)
{
return $this->addColumn('geometry', $column, compact('subtype', 'srid'));
}









public function geography($column, $subtype = null, $srid = 4326)
{
return $this->addColumn('geography', $column, compact('subtype', 'srid'));
}








public function computed($column, $expression)
{
return $this->addColumn('computed', $column, compact('expression'));
}








public function vector($column, $dimensions)
{
return $this->addColumn('vector', $column, compact('dimensions'));
}








public function morphs($name, $indexName = null)
{
if (Builder::$defaultMorphKeyType === 'uuid') {
$this->uuidMorphs($name, $indexName);
} elseif (Builder::$defaultMorphKeyType === 'ulid') {
$this->ulidMorphs($name, $indexName);
} else {
$this->numericMorphs($name, $indexName);
}
}








public function nullableMorphs($name, $indexName = null)
{
if (Builder::$defaultMorphKeyType === 'uuid') {
$this->nullableUuidMorphs($name, $indexName);
} elseif (Builder::$defaultMorphKeyType === 'ulid') {
$this->nullableUlidMorphs($name, $indexName);
} else {
$this->nullableNumericMorphs($name, $indexName);
}
}








public function numericMorphs($name, $indexName = null)
{
$this->string("{$name}_type");

$this->unsignedBigInteger("{$name}_id");

$this->index(["{$name}_type", "{$name}_id"], $indexName);
}








public function nullableNumericMorphs($name, $indexName = null)
{
$this->string("{$name}_type")->nullable();

$this->unsignedBigInteger("{$name}_id")->nullable();

$this->index(["{$name}_type", "{$name}_id"], $indexName);
}








public function uuidMorphs($name, $indexName = null)
{
$this->string("{$name}_type");

$this->uuid("{$name}_id");

$this->index(["{$name}_type", "{$name}_id"], $indexName);
}








public function nullableUuidMorphs($name, $indexName = null)
{
$this->string("{$name}_type")->nullable();

$this->uuid("{$name}_id")->nullable();

$this->index(["{$name}_type", "{$name}_id"], $indexName);
}








public function ulidMorphs($name, $indexName = null)
{
$this->string("{$name}_type");

$this->ulid("{$name}_id");

$this->index(["{$name}_type", "{$name}_id"], $indexName);
}








public function nullableUlidMorphs($name, $indexName = null)
{
$this->string("{$name}_type")->nullable();

$this->ulid("{$name}_id")->nullable();

$this->index(["{$name}_type", "{$name}_id"], $indexName);
}






public function rememberToken()
{
return $this->string('remember_token', 100)->nullable();
}







public function comment($comment)
{
return $this->addCommand('tableComment', compact('comment'));
}










protected function indexCommand($type, $columns, $index, $algorithm = null)
{
$columns = (array) $columns;




$index = $index ?: $this->createIndexName($type, $columns);

return $this->addCommand(
$type, compact('index', 'columns', 'algorithm')
);
}









protected function dropIndexCommand($command, $type, $index)
{
$columns = [];




if (is_array($index)) {
$index = $this->createIndexName($type, $columns = $index);
}

return $this->indexCommand($command, $columns, $index);
}








protected function createIndexName($type, array $columns)
{
$table = str_contains($this->table, '.')
? substr_replace($this->table, '.'.$this->prefix, strrpos($this->table, '.'), 1)
: $this->prefix.$this->table;

$index = strtolower($table.'_'.implode('_', $columns).'_'.$type);

return str_replace(['-', '.'], '_', $index);
}









public function addColumn($type, $name, array $parameters = [])
{
return $this->addColumnDefinition(new ColumnDefinition(
array_merge(compact('type', 'name'), $parameters)
));
}







protected function addColumnDefinition($definition)
{
$this->columns[] = $definition;

if (! $this->creating()) {
$this->commands[] = $definition;
}

if ($this->after) {
$definition->after($this->after);

$this->after = $definition->name;
}

return $definition;
}








public function after($column, Closure $callback)
{
$this->after = $column;

$callback($this);

$this->after = null;
}







public function removeColumn($name)
{
$this->columns = array_values(array_filter($this->columns, function ($c) use ($name) {
return $c['name'] != $name;
}));

$this->commands = array_values(array_filter($this->commands, function ($c) use ($name) {
return ! $c instanceof ColumnDefinition || $c['name'] != $name;
}));

return $this;
}








protected function addCommand($name, array $parameters = [])
{
$this->commands[] = $command = $this->createCommand($name, $parameters);

return $command;
}








protected function createCommand($name, array $parameters = [])
{
return new Fluent(array_merge(compact('name'), $parameters));
}






public function getTable()
{
return $this->table;
}






public function getPrefix()
{
return $this->prefix;
}






public function getColumns()
{
return $this->columns;
}






public function getCommands()
{
return $this->commands;
}







private function hasState(): bool
{
return ! is_null($this->state);
}






public function getState()
{
return $this->state;
}






public function getAddedColumns()
{
return array_filter($this->columns, function ($column) {
return ! $column->change;
});
}








public function getChangedColumns()
{
return array_filter($this->columns, function ($column) {
return (bool) $column->change;
});
}
}
