<?php

namespace Illuminate\Database\Schema\Grammars;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Fluent;
use RuntimeException;

class MySqlGrammar extends Grammar
{





protected $modifiers = [
'Unsigned', 'Charset', 'Collate', 'VirtualAs', 'StoredAs', 'Nullable',
'Default', 'OnUpdate', 'Invisible', 'Increment', 'Comment', 'After', 'First',
];






protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];






protected $fluentCommands = ['AutoIncrementStartingValues'];








public function compileCreateDatabase($name, $connection)
{
$charset = $connection->getConfig('charset');
$collation = $connection->getConfig('collation');

if (! $charset || ! $collation) {
return sprintf(
'create database %s',
$this->wrapValue($name),
);
}

return sprintf(
'create database %s default character set %s default collate %s',
$this->wrapValue($name),
$this->wrapValue($charset),
$this->wrapValue($collation),
);
}







public function compileDropDatabaseIfExists($name)
{
return sprintf(
'drop database if exists %s',
$this->wrapValue($name)
);
}








public function compileTableExists($database, $table)
{
return sprintf(
'select exists (select 1 from information_schema.tables where '
."table_schema = %s and table_name = %s and table_type in ('BASE TABLE', 'SYSTEM VERSIONED')) as `exists`",
$this->quoteString($database),
$this->quoteString($table)
);
}







public function compileTables($database)
{
return sprintf(
'select table_name as `name`, (data_length + index_length) as `size`, '
.'table_comment as `comment`, engine as `engine`, table_collation as `collation` '
."from information_schema.tables where table_schema = %s and table_type in ('BASE TABLE', 'SYSTEM VERSIONED') "
.'order by table_name',
$this->quoteString($database)
);
}







public function compileViews($database)
{
return sprintf(
'select table_name as `name`, view_definition as `definition` '
.'from information_schema.views where table_schema = %s '
.'order by table_name',
$this->quoteString($database)
);
}








public function compileColumns($database, $table)
{
return sprintf(
'select column_name as `name`, data_type as `type_name`, column_type as `type`, '
.'collation_name as `collation`, is_nullable as `nullable`, '
.'column_default as `default`, column_comment as `comment`, '
.'generation_expression as `expression`, extra as `extra` '
.'from information_schema.columns where table_schema = %s and table_name = %s '
.'order by ordinal_position asc',
$this->quoteString($database),
$this->quoteString($table)
);
}








public function compileIndexes($database, $table)
{
return sprintf(
'select index_name as `name`, group_concat(column_name order by seq_in_index) as `columns`, '
.'index_type as `type`, not non_unique as `unique` '
.'from information_schema.statistics where table_schema = %s and table_name = %s '
.'group by index_name, index_type, non_unique',
$this->quoteString($database),
$this->quoteString($table)
);
}








public function compileForeignKeys($database, $table)
{
return sprintf(
'select kc.constraint_name as `name`, '
.'group_concat(kc.column_name order by kc.ordinal_position) as `columns`, '
.'kc.referenced_table_schema as `foreign_schema`, '
.'kc.referenced_table_name as `foreign_table`, '
.'group_concat(kc.referenced_column_name order by kc.ordinal_position) as `foreign_columns`, '
.'rc.update_rule as `on_update`, '
.'rc.delete_rule as `on_delete` '
.'from information_schema.key_column_usage kc join information_schema.referential_constraints rc '
.'on kc.constraint_schema = rc.constraint_schema and kc.constraint_name = rc.constraint_name '
.'where kc.table_schema = %s and kc.table_name = %s and kc.referenced_table_name is not null '
.'group by kc.constraint_name, kc.referenced_table_schema, kc.referenced_table_name, rc.update_rule, rc.delete_rule',
$this->quoteString($database),
$this->quoteString($table)
);
}









public function compileCreate(Blueprint $blueprint, Fluent $command, Connection $connection)
{
$sql = $this->compileCreateTable(
$blueprint, $command, $connection
);




$sql = $this->compileCreateEncoding(
$sql, $connection, $blueprint
);




return $this->compileCreateEngine($sql, $connection, $blueprint);
}









protected function compileCreateTable($blueprint, $command, $connection)
{
$tableStructure = $this->getColumns($blueprint);

if ($primaryKey = $this->getCommandByName($blueprint, 'primary')) {
$tableStructure[] = sprintf(
'primary key %s(%s)',
$primaryKey->algorithm ? 'using '.$primaryKey->algorithm : '',
$this->columnize($primaryKey->columns)
);

$primaryKey->shouldBeSkipped = true;
}

return sprintf('%s table %s (%s)',
$blueprint->temporary ? 'create temporary' : 'create',
$this->wrapTable($blueprint),
implode(', ', $tableStructure)
);
}









protected function compileCreateEncoding($sql, Connection $connection, Blueprint $blueprint)
{



if (isset($blueprint->charset)) {
$sql .= ' default character set '.$blueprint->charset;
} elseif (! is_null($charset = $connection->getConfig('charset'))) {
$sql .= ' default character set '.$charset;
}




if (isset($blueprint->collation)) {
$sql .= " collate '{$blueprint->collation}'";
} elseif (! is_null($collation = $connection->getConfig('collation'))) {
$sql .= " collate '{$collation}'";
}

return $sql;
}









protected function compileCreateEngine($sql, Connection $connection, Blueprint $blueprint)
{
if (isset($blueprint->engine)) {
return $sql.' engine = '.$blueprint->engine;
} elseif (! is_null($engine = $connection->getConfig('engine'))) {
return $sql.' engine = '.$engine;
}

return $sql;
}








public function compileAdd(Blueprint $blueprint, Fluent $command)
{
return sprintf('alter table %s add %s',
$this->wrapTable($blueprint),
$this->getColumn($blueprint, $command->column)
);
}








public function compileAutoIncrementStartingValues(Blueprint $blueprint, Fluent $command)
{
if ($command->column->autoIncrement
&& $value = $command->column->get('startingValue', $command->column->get('from'))) {
return 'alter table '.$this->wrapTable($blueprint).' auto_increment = '.$value;
}
}









public function compileRenameColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
{
$version = $connection->getServerVersion();

if (($connection->isMaria() && version_compare($version, '10.5.2', '<')) ||
(! $connection->isMaria() && version_compare($version, '8.0.3', '<'))) {
return $this->compileLegacyRenameColumn($blueprint, $command, $connection);
}

return parent::compileRenameColumn($blueprint, $command, $connection);
}









protected function compileLegacyRenameColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
{
$column = collect($connection->getSchemaBuilder()->getColumns($blueprint->getTable()))
->firstWhere('name', $command->from);

$modifiers = $this->addModifiers($column['type'], $blueprint, new ColumnDefinition([
'change' => true,
'type' => match ($column['type_name']) {
'bigint' => 'bigInteger',
'int' => 'integer',
'mediumint' => 'mediumInteger',
'smallint' => 'smallInteger',
'tinyint' => 'tinyInteger',
default => $column['type_name'],
},
'nullable' => $column['nullable'],
'default' => $column['default'] && (str_starts_with(strtolower($column['default']), 'current_timestamp') || $column['default'] === 'NULL')
? new Expression($column['default'])
: $column['default'],
'autoIncrement' => $column['auto_increment'],
'collation' => $column['collation'],
'comment' => $column['comment'],
'virtualAs' => ! is_null($column['generation']) && $column['generation']['type'] === 'virtual'
? $column['generation']['expression'] : null,
'storedAs' => ! is_null($column['generation']) && $column['generation']['type'] === 'stored'
? $column['generation']['expression'] : null,
]));

return sprintf('alter table %s change %s %s %s',
$this->wrapTable($blueprint),
$this->wrap($command->from),
$this->wrap($command->to),
$modifiers
);
}











public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection)
{
$column = $command->column;

$sql = sprintf('alter table %s %s %s%s %s',
$this->wrapTable($blueprint),
is_null($column->renameTo) ? 'modify' : 'change',
$this->wrap($column),
is_null($column->renameTo) ? '' : ' '.$this->wrap($column->renameTo),
$this->getType($column)
);

return $this->addModifiers($sql, $blueprint, $column);
}








public function compilePrimary(Blueprint $blueprint, Fluent $command)
{
return sprintf('alter table %s add primary key %s(%s)',
$this->wrapTable($blueprint),
$command->algorithm ? 'using '.$command->algorithm : '',
$this->columnize($command->columns)
);
}








public function compileUnique(Blueprint $blueprint, Fluent $command)
{
return $this->compileKey($blueprint, $command, 'unique');
}








public function compileIndex(Blueprint $blueprint, Fluent $command)
{
return $this->compileKey($blueprint, $command, 'index');
}








public function compileFullText(Blueprint $blueprint, Fluent $command)
{
return $this->compileKey($blueprint, $command, 'fulltext');
}








public function compileSpatialIndex(Blueprint $blueprint, Fluent $command)
{
return $this->compileKey($blueprint, $command, 'spatial index');
}









protected function compileKey(Blueprint $blueprint, Fluent $command, $type)
{
return sprintf('alter table %s add %s %s%s(%s)',
$this->wrapTable($blueprint),
$type,
$this->wrap($command->index),
$command->algorithm ? ' using '.$command->algorithm : '',
$this->columnize($command->columns)
);
}








public function compileDrop(Blueprint $blueprint, Fluent $command)
{
return 'drop table '.$this->wrapTable($blueprint);
}








public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
{
return 'drop table if exists '.$this->wrapTable($blueprint);
}








public function compileDropColumn(Blueprint $blueprint, Fluent $command)
{
$columns = $this->prefixArray('drop', $this->wrapArray($command->columns));

return 'alter table '.$this->wrapTable($blueprint).' '.implode(', ', $columns);
}








public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
{
return 'alter table '.$this->wrapTable($blueprint).' drop primary key';
}








public function compileDropUnique(Blueprint $blueprint, Fluent $command)
{
$index = $this->wrap($command->index);

return "alter table {$this->wrapTable($blueprint)} drop index {$index}";
}








public function compileDropIndex(Blueprint $blueprint, Fluent $command)
{
$index = $this->wrap($command->index);

return "alter table {$this->wrapTable($blueprint)} drop index {$index}";
}








public function compileDropFullText(Blueprint $blueprint, Fluent $command)
{
return $this->compileDropIndex($blueprint, $command);
}








public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command)
{
return $this->compileDropIndex($blueprint, $command);
}








public function compileDropForeign(Blueprint $blueprint, Fluent $command)
{
$index = $this->wrap($command->index);

return "alter table {$this->wrapTable($blueprint)} drop foreign key {$index}";
}








public function compileRename(Blueprint $blueprint, Fluent $command)
{
$from = $this->wrapTable($blueprint);

return "rename table {$from} to ".$this->wrapTable($command->to);
}








public function compileRenameIndex(Blueprint $blueprint, Fluent $command)
{
return sprintf('alter table %s rename index %s to %s',
$this->wrapTable($blueprint),
$this->wrap($command->from),
$this->wrap($command->to)
);
}







public function compileDropAllTables($tables)
{
return 'drop table '.implode(',', $this->wrapArray($tables));
}







public function compileDropAllViews($views)
{
return 'drop view '.implode(',', $this->wrapArray($views));
}






public function compileEnableForeignKeyConstraints()
{
return 'SET FOREIGN_KEY_CHECKS=1;';
}






public function compileDisableForeignKeyConstraints()
{
return 'SET FOREIGN_KEY_CHECKS=0;';
}








public function compileTableComment(Blueprint $blueprint, Fluent $command)
{
return sprintf('alter table %s comment = %s',
$this->wrapTable($blueprint),
"'".str_replace("'", "''", $command->comment)."'"
);
}







protected function typeChar(Fluent $column)
{
return "char({$column->length})";
}







protected function typeString(Fluent $column)
{
return "varchar({$column->length})";
}







protected function typeTinyText(Fluent $column)
{
return 'tinytext';
}







protected function typeText(Fluent $column)
{
return 'text';
}







protected function typeMediumText(Fluent $column)
{
return 'mediumtext';
}







protected function typeLongText(Fluent $column)
{
return 'longtext';
}







protected function typeBigInteger(Fluent $column)
{
return 'bigint';
}







protected function typeInteger(Fluent $column)
{
return 'int';
}







protected function typeMediumInteger(Fluent $column)
{
return 'mediumint';
}







protected function typeTinyInteger(Fluent $column)
{
return 'tinyint';
}







protected function typeSmallInteger(Fluent $column)
{
return 'smallint';
}







protected function typeFloat(Fluent $column)
{
if ($column->precision) {
return "float({$column->precision})";
}

return 'float';
}







protected function typeDouble(Fluent $column)
{
return 'double';
}







protected function typeDecimal(Fluent $column)
{
return "decimal({$column->total}, {$column->places})";
}







protected function typeBoolean(Fluent $column)
{
return 'tinyint(1)';
}







protected function typeEnum(Fluent $column)
{
return sprintf('enum(%s)', $this->quoteString($column->allowed));
}







protected function typeSet(Fluent $column)
{
return sprintf('set(%s)', $this->quoteString($column->allowed));
}







protected function typeJson(Fluent $column)
{
return 'json';
}







protected function typeJsonb(Fluent $column)
{
return 'json';
}







protected function typeDate(Fluent $column)
{
return 'date';
}







protected function typeDateTime(Fluent $column)
{
$current = $column->precision ? "CURRENT_TIMESTAMP($column->precision)" : 'CURRENT_TIMESTAMP';

if ($column->useCurrent) {
$column->default(new Expression($current));
}

if ($column->useCurrentOnUpdate) {
$column->onUpdate(new Expression($current));
}

return $column->precision ? "datetime($column->precision)" : 'datetime';
}







protected function typeDateTimeTz(Fluent $column)
{
return $this->typeDateTime($column);
}







protected function typeTime(Fluent $column)
{
return $column->precision ? "time($column->precision)" : 'time';
}







protected function typeTimeTz(Fluent $column)
{
return $this->typeTime($column);
}







protected function typeTimestamp(Fluent $column)
{
$current = $column->precision ? "CURRENT_TIMESTAMP($column->precision)" : 'CURRENT_TIMESTAMP';

if ($column->useCurrent) {
$column->default(new Expression($current));
}

if ($column->useCurrentOnUpdate) {
$column->onUpdate(new Expression($current));
}

return $column->precision ? "timestamp($column->precision)" : 'timestamp';
}







protected function typeTimestampTz(Fluent $column)
{
return $this->typeTimestamp($column);
}







protected function typeYear(Fluent $column)
{
return 'year';
}







protected function typeBinary(Fluent $column)
{
if ($column->length) {
return $column->fixed ? "binary({$column->length})" : "varbinary({$column->length})";
}

return 'blob';
}







protected function typeUuid(Fluent $column)
{
return 'char(36)';
}







protected function typeIpAddress(Fluent $column)
{
return 'varchar(45)';
}







protected function typeMacAddress(Fluent $column)
{
return 'varchar(17)';
}







protected function typeGeometry(Fluent $column)
{
$subtype = $column->subtype ? strtolower($column->subtype) : null;

if (! in_array($subtype, ['point', 'linestring', 'polygon', 'geometrycollection', 'multipoint', 'multilinestring', 'multipolygon'])) {
$subtype = null;
}

return sprintf('%s%s',
$subtype ?? 'geometry',
match (true) {
$column->srid && $this->connection?->isMaria() => ' ref_system_id='.$column->srid,
(bool) $column->srid => ' srid '.$column->srid,
default => '',
}
);
}







protected function typeGeography(Fluent $column)
{
return $this->typeGeometry($column);
}









protected function typeComputed(Fluent $column)
{
throw new RuntimeException('This database driver requires a type, see the virtualAs / storedAs modifiers.');
}







protected function typeVector(Fluent $column)
{
return "vector($column->dimensions)";
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
return " as ({$this->getValue($storedAs)}) stored";
}
}








protected function modifyUnsigned(Blueprint $blueprint, Fluent $column)
{
if ($column->unsigned) {
return ' unsigned';
}
}








protected function modifyCharset(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->charset)) {
return ' character set '.$column->charset;
}
}








protected function modifyCollate(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->collation)) {
return " collate '{$column->collation}'";
}
}








protected function modifyNullable(Blueprint $blueprint, Fluent $column)
{
if (is_null($column->virtualAs) &&
is_null($column->virtualAsJson) &&
is_null($column->storedAs) &&
is_null($column->storedAsJson)) {
return $column->nullable ? ' null' : ' not null';
}

if ($column->nullable === false) {
return ' not null';
}
}








protected function modifyInvisible(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->invisible)) {
return ' invisible';
}
}








protected function modifyDefault(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->default)) {
return ' default '.$this->getDefaultValue($column->default);
}
}








protected function modifyOnUpdate(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->onUpdate)) {
return ' on update '.$this->getValue($column->onUpdate);
}
}








protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
{
if (in_array($column->type, $this->serials) && $column->autoIncrement) {
return $this->hasCommand($blueprint, 'primary') || ($column->change && ! $column->primary)
? ' auto_increment'
: ' auto_increment primary key';
}
}








protected function modifyFirst(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->first)) {
return ' first';
}
}








protected function modifyAfter(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->after)) {
return ' after '.$this->wrap($column->after);
}
}








protected function modifyComment(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->comment)) {
return " comment '".addslashes($column->comment)."'";
}
}







protected function wrapValue($value)
{
if ($value !== '*') {
return '`'.str_replace('`', '``', $value).'`';
}

return $value;
}







protected function wrapJsonSelector($value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($value);

return 'json_unquote(json_extract('.$field.$path.'))';
}
}
