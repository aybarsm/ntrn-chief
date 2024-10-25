<?php

namespace Illuminate\Database\Schema\Grammars;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\IndexDefinition;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use RuntimeException;

class SQLiteGrammar extends Grammar
{





protected $modifiers = ['Increment', 'Nullable', 'Default', 'Collate', 'VirtualAs', 'StoredAs'];






protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];







public function getAlterCommands(Connection $connection)
{
$alterCommands = ['change', 'primary', 'dropPrimary', 'foreign', 'dropForeign'];

if (version_compare($connection->getServerVersion(), '3.35', '<')) {
$alterCommands[] = 'dropColumn';
}

return $alterCommands;
}








public function compileSqlCreateStatement($name, $type = 'table')
{
return sprintf('select "sql" from sqlite_master where type = %s and name = %s',
$this->quoteString($type),
$this->quoteString(str_replace('.', '__', $name))
);
}






public function compileDbstatExists()
{
return "select exists (select 1 from pragma_compile_options where compile_options = 'ENABLE_DBSTAT_VTAB') as enabled";
}







public function compileTableExists($table)
{
return sprintf(
'select exists (select 1 from sqlite_master where name = %s and type = \'table\') as "exists"',
$this->quoteString(str_replace('.', '__', $table))
);
}







public function compileTables($withSize = false)
{
return $withSize
? 'select m.tbl_name as name, sum(s.pgsize) as size from sqlite_master as m '
.'join dbstat as s on s.name = m.name '
."where m.type in ('table', 'index') and m.tbl_name not like 'sqlite_%' "
.'group by m.tbl_name '
.'order by m.tbl_name'
: "select name from sqlite_master where type = 'table' and name not like 'sqlite_%' order by name";
}






public function compileViews()
{
return "select name, sql as definition from sqlite_master where type = 'view' order by name";
}







public function compileColumns($table)
{
return sprintf(
'select name, type, not "notnull" as "nullable", dflt_value as "default", pk as "primary", hidden as "extra" '
.'from pragma_table_xinfo(%s) order by cid asc',
$this->quoteString(str_replace('.', '__', $table))
);
}







public function compileIndexes($table)
{
return sprintf(
'select \'primary\' as name, group_concat(col) as columns, 1 as "unique", 1 as "primary" '
.'from (select name as col from pragma_table_info(%s) where pk > 0 order by pk, cid) group by name '
.'union select name, group_concat(col) as columns, "unique", origin = \'pk\' as "primary" '
.'from (select il.*, ii.name as col from pragma_index_list(%s) il, pragma_index_info(il.name) ii order by il.seq, ii.seqno) '
.'group by name, "unique", "primary"',
$table = $this->quoteString(str_replace('.', '__', $table)),
$table
);
}







public function compileForeignKeys($table)
{
return sprintf(
'select group_concat("from") as columns, "table" as foreign_table, '
.'group_concat("to") as foreign_columns, on_update, on_delete '
.'from (select * from pragma_foreign_key_list(%s) order by id desc, seq) '
.'group by id, "table", on_update, on_delete',
$this->quoteString(str_replace('.', '__', $table))
);
}








public function compileCreate(Blueprint $blueprint, Fluent $command)
{
return sprintf('%s table %s (%s%s%s)',
$blueprint->temporary ? 'create temporary' : 'create',
$this->wrapTable($blueprint),
implode(', ', $this->getColumns($blueprint)),
$this->addForeignKeys($this->getCommandsByName($blueprint, 'foreign')),
$this->addPrimaryKeys($this->getCommandByName($blueprint, 'primary'))
);
}







protected function addForeignKeys($foreignKeys)
{
return collect($foreignKeys)->reduce(function ($sql, $foreign) {



return $sql.$this->getForeignKey($foreign);
}, '');
}







protected function getForeignKey($foreign)
{



$sql = sprintf(', foreign key(%s) references %s(%s)',
$this->columnize($foreign->columns),
$this->wrapTable($foreign->on),
$this->columnize((array) $foreign->references)
);

if (! is_null($foreign->onDelete)) {
$sql .= " on delete {$foreign->onDelete}";
}




if (! is_null($foreign->onUpdate)) {
$sql .= " on update {$foreign->onUpdate}";
}

return $sql;
}







protected function addPrimaryKeys($primary)
{
if (! is_null($primary)) {
return ", primary key ({$this->columnize($primary->columns)})";
}
}








public function compileAdd(Blueprint $blueprint, Fluent $command)
{
return sprintf('alter table %s add column %s',
$this->wrapTable($blueprint),
$this->getColumn($blueprint, $command->column)
);
}











public function compileAlter(Blueprint $blueprint, Fluent $command, Connection $connection)
{
$columnNames = [];
$autoIncrementColumn = null;

$columns = collect($blueprint->getState()->getColumns())
->map(function ($column) use ($blueprint, &$columnNames, &$autoIncrementColumn) {
$name = $this->wrap($column);

$autoIncrementColumn = $column->autoIncrement ? $column->name : $autoIncrementColumn;

if (is_null($column->virtualAs) && is_null($column->virtualAsJson) &&
is_null($column->storedAs) && is_null($column->storedAsJson)) {
$columnNames[] = $name;
}

return $this->addModifiers(
$this->wrap($column).' '.($column->full_type_definition ?? $this->getType($column)),
$blueprint,
$column
);
})->all();

$indexes = collect($blueprint->getState()->getIndexes())
->reject(fn ($index) => str_starts_with('sqlite_', $index->index))
->map(fn ($index) => $this->{'compile'.ucfirst($index->name)}($blueprint, $index))
->all();

$tempTable = $this->wrap('__temp__'.$blueprint->getPrefix().$blueprint->getTable());
$table = $this->wrapTable($blueprint);
$columnNames = implode(', ', $columnNames);

$foreignKeyConstraintsEnabled = $connection->scalar('pragma foreign_keys');

return array_filter(array_merge([
$foreignKeyConstraintsEnabled ? $this->compileDisableForeignKeyConstraints() : null,
sprintf('create table %s (%s%s%s)',
$tempTable,
implode(', ', $columns),
$this->addForeignKeys($blueprint->getState()->getForeignKeys()),
$autoIncrementColumn ? '' : $this->addPrimaryKeys($blueprint->getState()->getPrimaryKey())
),
sprintf('insert into %s (%s) select %s from %s', $tempTable, $columnNames, $columnNames, $table),
sprintf('drop table %s', $table),
sprintf('alter table %s rename to %s', $tempTable, $table),
], $indexes, [$foreignKeyConstraintsEnabled ? $this->compileEnableForeignKeyConstraints() : null]));
}











public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection)
{

}








public function compilePrimary(Blueprint $blueprint, Fluent $command)
{

}








public function compileUnique(Blueprint $blueprint, Fluent $command)
{
return sprintf('create unique index %s on %s (%s)',
$this->wrap($command->index),
$this->wrapTable($blueprint),
$this->columnize($command->columns)
);
}








public function compileIndex(Blueprint $blueprint, Fluent $command)
{
return sprintf('create index %s on %s (%s)',
$this->wrap($command->index),
$this->wrapTable($blueprint),
$this->columnize($command->columns)
);
}










public function compileSpatialIndex(Blueprint $blueprint, Fluent $command)
{
throw new RuntimeException('The database driver in use does not support spatial indexes.');
}








public function compileForeign(Blueprint $blueprint, Fluent $command)
{

}








public function compileDrop(Blueprint $blueprint, Fluent $command)
{
return 'drop table '.$this->wrapTable($blueprint);
}








public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
{
return 'drop table if exists '.$this->wrapTable($blueprint);
}






public function compileDropAllTables()
{
return "delete from sqlite_master where type in ('table', 'index', 'trigger')";
}






public function compileDropAllViews()
{
return "delete from sqlite_master where type in ('view')";
}






public function compileRebuild()
{
return 'vacuum';
}









public function compileDropColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
{
if (version_compare($connection->getServerVersion(), '3.35', '<')) {


return null;
}

$table = $this->wrapTable($blueprint);

$columns = $this->prefixArray('drop column', $this->wrapArray($command->columns));

return collect($columns)->map(fn ($column) => 'alter table '.$table.' '.$column)->all();
}








public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
{

}








public function compileDropUnique(Blueprint $blueprint, Fluent $command)
{
$index = $this->wrap($command->index);

return "drop index {$index}";
}








public function compileDropIndex(Blueprint $blueprint, Fluent $command)
{
$index = $this->wrap($command->index);

return "drop index {$index}";
}










public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command)
{
throw new RuntimeException('The database driver in use does not support spatial indexes.');
}








public function compileDropForeign(Blueprint $blueprint, Fluent $command)
{
if (empty($command->columns)) {
throw new RuntimeException('This database driver does not support dropping foreign keys by name.');
}


}








public function compileRename(Blueprint $blueprint, Fluent $command)
{
$from = $this->wrapTable($blueprint);

return "alter table {$from} rename to ".$this->wrapTable($command->to);
}











public function compileRenameIndex(Blueprint $blueprint, Fluent $command, Connection $connection)
{
$indexes = $connection->getSchemaBuilder()->getIndexes($blueprint->getTable());

$index = Arr::first($indexes, fn ($index) => $index['name'] === $command->from);

if (! $index) {
throw new RuntimeException("Index [{$command->from}] does not exist.");
}

if ($index['primary']) {
throw new RuntimeException('SQLite does not support altering primary keys.');
}

if ($index['unique']) {
return [
$this->compileDropUnique($blueprint, new IndexDefinition(['index' => $index['name']])),
$this->compileUnique($blueprint,
new IndexDefinition(['index' => $command->to, 'columns' => $index['columns']])
),
];
}

return [
$this->compileDropIndex($blueprint, new IndexDefinition(['index' => $index['name']])),
$this->compileIndex($blueprint,
new IndexDefinition(['index' => $command->to, 'columns' => $index['columns']])
),
];
}






public function compileEnableForeignKeyConstraints()
{
return $this->pragma('foreign_keys', 'ON');
}






public function compileDisableForeignKeyConstraints()
{
return $this->pragma('foreign_keys', 'OFF');
}







public function compileSetBusyTimeout($milliseconds)
{
return $this->pragma('busy_timeout', $milliseconds);
}







public function compileSetJournalMode($mode)
{
return $this->pragma('journal_mode', $mode);
}







public function compileSetSynchronous($mode)
{
return $this->pragma('synchronous', $mode);
}






public function compileEnableWriteableSchema()
{
return $this->pragma('writable_schema', 1);
}






public function compileDisableWriteableSchema()
{
return $this->pragma('writable_schema', 0);
}








protected function pragma(string $name, mixed $value): string
{
return sprintf('PRAGMA %s = %s;', $name, $value);
}







protected function typeChar(Fluent $column)
{
return 'varchar';
}







protected function typeString(Fluent $column)
{
return 'varchar';
}







protected function typeTinyText(Fluent $column)
{
return 'text';
}







protected function typeText(Fluent $column)
{
return 'text';
}







protected function typeMediumText(Fluent $column)
{
return 'text';
}







protected function typeLongText(Fluent $column)
{
return 'text';
}







protected function typeInteger(Fluent $column)
{
return 'integer';
}







protected function typeBigInteger(Fluent $column)
{
return 'integer';
}







protected function typeMediumInteger(Fluent $column)
{
return 'integer';
}







protected function typeTinyInteger(Fluent $column)
{
return 'integer';
}







protected function typeSmallInteger(Fluent $column)
{
return 'integer';
}







protected function typeFloat(Fluent $column)
{
return 'float';
}







protected function typeDouble(Fluent $column)
{
return 'double';
}







protected function typeDecimal(Fluent $column)
{
return 'numeric';
}







protected function typeBoolean(Fluent $column)
{
return 'tinyint(1)';
}







protected function typeEnum(Fluent $column)
{
return sprintf(
'varchar check ("%s" in (%s))',
$column->name,
$this->quoteString($column->allowed)
);
}







protected function typeJson(Fluent $column)
{
return 'text';
}







protected function typeJsonb(Fluent $column)
{
return 'text';
}







protected function typeDate(Fluent $column)
{
return 'date';
}







protected function typeDateTime(Fluent $column)
{
return $this->typeTimestamp($column);
}











protected function typeDateTimeTz(Fluent $column)
{
return $this->typeDateTime($column);
}







protected function typeTime(Fluent $column)
{
return 'time';
}







protected function typeTimeTz(Fluent $column)
{
return $this->typeTime($column);
}







protected function typeTimestamp(Fluent $column)
{
if ($column->useCurrent) {
$column->default(new Expression('CURRENT_TIMESTAMP'));
}

return 'datetime';
}







protected function typeTimestampTz(Fluent $column)
{
return $this->typeTimestamp($column);
}







protected function typeYear(Fluent $column)
{
return $this->typeInteger($column);
}







protected function typeBinary(Fluent $column)
{
return 'blob';
}







protected function typeUuid(Fluent $column)
{
return 'varchar';
}







protected function typeIpAddress(Fluent $column)
{
return 'varchar';
}







protected function typeMacAddress(Fluent $column)
{
return 'varchar';
}







protected function typeGeometry(Fluent $column)
{
return 'geometry';
}







protected function typeGeography(Fluent $column)
{
return $this->typeGeometry($column);
}









protected function typeComputed(Fluent $column)
{
throw new RuntimeException('This database driver requires a type, see the virtualAs / storedAs modifiers.');
}








protected function modifyVirtualAs(Blueprint $blueprint, Fluent $column)
{
if (! is_null($virtualAs = $column->virtualAsJson)) {
if ($this->isJsonSelector($virtualAs)) {
$virtualAs = $this->wrapJsonSelector($virtualAs);
}

return " as ({$virtualAs})";
}

if (! is_null($virtualAs = $column->virtualAs)) {
return " as ({$this->getValue($virtualAs)})";
}
}








protected function modifyStoredAs(Blueprint $blueprint, Fluent $column)
{
if (! is_null($storedAs = $column->storedAsJson)) {
if ($this->isJsonSelector($storedAs)) {
$storedAs = $this->wrapJsonSelector($storedAs);
}

return " as ({$storedAs}) stored";
}

if (! is_null($storedAs = $column->storedAs)) {
return " as ({$this->getValue($column->storedAs)}) stored";
}
}








protected function modifyNullable(Blueprint $blueprint, Fluent $column)
{
if (is_null($column->virtualAs) &&
is_null($column->virtualAsJson) &&
is_null($column->storedAs) &&
is_null($column->storedAsJson)) {
return $column->nullable ? '' : ' not null';
}

if ($column->nullable === false) {
return ' not null';
}
}








protected function modifyDefault(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->default) && is_null($column->virtualAs) && is_null($column->virtualAsJson) && is_null($column->storedAs)) {
return ' default '.$this->getDefaultValue($column->default);
}
}








protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
{
if (in_array($column->type, $this->serials) && $column->autoIncrement) {
return ' primary key autoincrement';
}
}








protected function modifyCollate(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->collation)) {
return " collate '{$column->collation}'";
}
}







protected function wrapJsonSelector($value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($value);

return 'json_extract('.$field.$path.')';
}
}
