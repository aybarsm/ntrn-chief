<?php

namespace Illuminate\Database\Schema\Grammars;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;

class SqlServerGrammar extends Grammar
{





protected $transactions = true;






protected $modifiers = ['Collate', 'Nullable', 'Default', 'Persisted', 'Increment'];






protected $serials = ['tinyInteger', 'smallInteger', 'mediumInteger', 'integer', 'bigInteger'];






protected $fluentCommands = ['Default'];






public function compileDefaultSchema()
{
return 'select schema_name()';
}








public function compileCreateDatabase($name, $connection)
{
return sprintf(
'create database %s',
$this->wrapValue($name),
);
}







public function compileDropDatabaseIfExists($name)
{
return sprintf(
'drop database if exists %s',
$this->wrapValue($name)
);
}








public function compileTableExists($schema, $table)
{
return sprintf(
'select (case when object_id(%s, \'U\') is null then 0 else 1 end) as [exists]',
$this->quoteString($schema ? $schema.'.'.$table : $table)
);
}






public function compileTables()
{
return 'select t.name as name, schema_name(t.schema_id) as [schema], sum(u.total_pages) * 8 * 1024 as size '
.'from sys.tables as t '
.'join sys.partitions as p on p.object_id = t.object_id '
.'join sys.allocation_units as u on u.container_id = p.hobt_id '
.'group by t.name, t.schema_id '
.'order by t.name';
}






public function compileViews()
{
return 'select name, schema_name(v.schema_id) as [schema], definition from sys.views as v '
.'inner join sys.sql_modules as m on v.object_id = m.object_id '
.'order by name';
}








public function compileColumns($schema, $table)
{
return sprintf(
'select col.name, type.name as type_name, '
.'col.max_length as length, col.precision as precision, col.scale as places, '
.'col.is_nullable as nullable, def.definition as [default], '
.'col.is_identity as autoincrement, col.collation_name as collation, '
.'com.definition as [expression], is_persisted as [persisted], '
.'cast(prop.value as nvarchar(max)) as comment '
.'from sys.columns as col '
.'join sys.types as type on col.user_type_id = type.user_type_id '
.'join sys.objects as obj on col.object_id = obj.object_id '
.'join sys.schemas as scm on obj.schema_id = scm.schema_id '
.'left join sys.default_constraints def on col.default_object_id = def.object_id and col.object_id = def.parent_object_id '
."left join sys.extended_properties as prop on obj.object_id = prop.major_id and col.column_id = prop.minor_id and prop.name = 'MS_Description' "
.'left join sys.computed_columns as com on col.column_id = com.column_id and col.object_id = com.object_id '
."where obj.type in ('U', 'V') and obj.name = %s and scm.name = %s "
.'order by col.column_id',
$this->quoteString($table),
$schema ? $this->quoteString($schema) : 'schema_name()',
);
}








public function compileIndexes($schema, $table)
{
return sprintf(
"select idx.name as name, string_agg(col.name, ',') within group (order by idxcol.key_ordinal) as columns, "
.'idx.type_desc as [type], idx.is_unique as [unique], idx.is_primary_key as [primary] '
.'from sys.indexes as idx '
.'join sys.tables as tbl on idx.object_id = tbl.object_id '
.'join sys.schemas as scm on tbl.schema_id = scm.schema_id '
.'join sys.index_columns as idxcol on idx.object_id = idxcol.object_id and idx.index_id = idxcol.index_id '
.'join sys.columns as col on idxcol.object_id = col.object_id and idxcol.column_id = col.column_id '
.'where tbl.name = %s and scm.name = %s '
.'group by idx.name, idx.type_desc, idx.is_unique, idx.is_primary_key',
$this->quoteString($table),
$schema ? $this->quoteString($schema) : 'schema_name()',
);
}








public function compileForeignKeys($schema, $table)
{
return sprintf(
'select fk.name as name, '
."string_agg(lc.name, ',') within group (order by fkc.constraint_column_id) as columns, "
.'fs.name as foreign_schema, ft.name as foreign_table, '
."string_agg(fc.name, ',') within group (order by fkc.constraint_column_id) as foreign_columns, "
.'fk.update_referential_action_desc as on_update, '
.'fk.delete_referential_action_desc as on_delete '
.'from sys.foreign_keys as fk '
.'join sys.foreign_key_columns as fkc on fkc.constraint_object_id = fk.object_id '
.'join sys.tables as lt on lt.object_id = fk.parent_object_id '
.'join sys.schemas as ls on lt.schema_id = ls.schema_id '
.'join sys.columns as lc on fkc.parent_object_id = lc.object_id and fkc.parent_column_id = lc.column_id '
.'join sys.tables as ft on ft.object_id = fk.referenced_object_id '
.'join sys.schemas as fs on ft.schema_id = fs.schema_id '
.'join sys.columns as fc on fkc.referenced_object_id = fc.object_id and fkc.referenced_column_id = fc.column_id '
.'where lt.name = %s and ls.name = %s '
.'group by fk.name, fs.name, ft.name, fk.update_referential_action_desc, fk.delete_referential_action_desc',
$this->quoteString($table),
$schema ? $this->quoteString($schema) : 'schema_name()',
);
}








public function compileCreate(Blueprint $blueprint, Fluent $command)
{
$columns = implode(', ', $this->getColumns($blueprint));

return 'create table '.$this->wrapTable($blueprint)." ($columns)";
}








public function compileAdd(Blueprint $blueprint, Fluent $command)
{
return sprintf('alter table %s add %s',
$this->wrapTable($blueprint),
$this->getColumn($blueprint, $command->column)
);
}









public function compileRenameColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
{
return sprintf("sp_rename %s, %s, N'COLUMN'",
$this->quoteString($this->wrapTable($blueprint).'.'.$this->wrap($command->from)),
$this->wrap($command->to)
);
}











public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection)
{
return [
$this->compileDropDefaultConstraint($blueprint, $command),
sprintf('alter table %s alter column %s',
$this->wrapTable($blueprint),
$this->getColumn($blueprint, $command->column),
),
];
}








public function compilePrimary(Blueprint $blueprint, Fluent $command)
{
return sprintf('alter table %s add constraint %s primary key (%s)',
$this->wrapTable($blueprint),
$this->wrap($command->index),
$this->columnize($command->columns)
);
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
return sprintf('create spatial index %s on %s (%s)',
$this->wrap($command->index),
$this->wrapTable($blueprint),
$this->columnize($command->columns)
);
}








public function compileDefault(Blueprint $blueprint, Fluent $command)
{
if ($command->column->change && ! is_null($command->column->default)) {
return sprintf('alter table %s add default %s for %s',
$this->wrapTable($blueprint),
$this->getDefaultValue($command->column->default),
$this->wrap($command->column)
);
}
}








public function compileDrop(Blueprint $blueprint, Fluent $command)
{
return 'drop table '.$this->wrapTable($blueprint);
}








public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
{
return sprintf('if object_id(%s, \'U\') is not null drop table %s',
$this->quoteString($this->wrapTable($blueprint)),
$this->wrapTable($blueprint)
);
}






public function compileDropAllTables()
{
return "EXEC sp_msforeachtable 'DROP TABLE ?'";
}








public function compileDropColumn(Blueprint $blueprint, Fluent $command)
{
$columns = $this->wrapArray($command->columns);

$dropExistingConstraintsSql = $this->compileDropDefaultConstraint($blueprint, $command).';';

return $dropExistingConstraintsSql.'alter table '.$this->wrapTable($blueprint).' drop column '.implode(', ', $columns);
}








public function compileDropDefaultConstraint(Blueprint $blueprint, Fluent $command)
{
$columns = $command->name === 'change'
? "'".$command->column->name."'"
: "'".implode("','", $command->columns)."'";

$table = $this->wrapTable($blueprint);
$tableName = $this->quoteString($this->wrapTable($blueprint));

$sql = "DECLARE @sql NVARCHAR(MAX) = '';";
$sql .= "SELECT @sql += 'ALTER TABLE $table DROP CONSTRAINT ' + OBJECT_NAME([default_object_id]) + ';' ";
$sql .= 'FROM sys.columns ';
$sql .= "WHERE [object_id] = OBJECT_ID($tableName) AND [name] in ($columns) AND [default_object_id] <> 0;";
$sql .= 'EXEC(@sql)';

return $sql;
}








public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
{
$index = $this->wrap($command->index);

return "alter table {$this->wrapTable($blueprint)} drop constraint {$index}";
}








public function compileDropUnique(Blueprint $blueprint, Fluent $command)
{
$index = $this->wrap($command->index);

return "drop index {$index} on {$this->wrapTable($blueprint)}";
}








public function compileDropIndex(Blueprint $blueprint, Fluent $command)
{
$index = $this->wrap($command->index);

return "drop index {$index} on {$this->wrapTable($blueprint)}";
}








public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command)
{
return $this->compileDropIndex($blueprint, $command);
}








public function compileDropForeign(Blueprint $blueprint, Fluent $command)
{
$index = $this->wrap($command->index);

return "alter table {$this->wrapTable($blueprint)} drop constraint {$index}";
}








public function compileRename(Blueprint $blueprint, Fluent $command)
{
return sprintf('sp_rename %s, %s',
$this->quoteString($this->wrapTable($blueprint)),
$this->wrapTable($command->to)
);
}








public function compileRenameIndex(Blueprint $blueprint, Fluent $command)
{
return sprintf("sp_rename %s, %s, N'INDEX'",
$this->quoteString($this->wrapTable($blueprint).'.'.$this->wrap($command->from)),
$this->wrap($command->to)
);
}






public function compileEnableForeignKeyConstraints()
{
return 'EXEC sp_msforeachtable @command1="print \'?\'", @command2="ALTER TABLE ? WITH CHECK CHECK CONSTRAINT all";';
}






public function compileDisableForeignKeyConstraints()
{
return 'EXEC sp_msforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT all";';
}






public function compileDropAllForeignKeys()
{
return "DECLARE @sql NVARCHAR(MAX) = N'';
            SELECT @sql += 'ALTER TABLE '
                + QUOTENAME(OBJECT_SCHEMA_NAME(parent_object_id)) + '.' + + QUOTENAME(OBJECT_NAME(parent_object_id))
                + ' DROP CONSTRAINT ' + QUOTENAME(name) + ';'
            FROM sys.foreign_keys;

            EXEC sp_executesql @sql;";
}






public function compileDropAllViews()
{
return "DECLARE @sql NVARCHAR(MAX) = N'';
            SELECT @sql += 'DROP VIEW ' + QUOTENAME(OBJECT_SCHEMA_NAME(object_id)) + '.' + QUOTENAME(name) + ';'
            FROM sys.views;

            EXEC sp_executesql @sql;";
}







protected function typeChar(Fluent $column)
{
return "nchar({$column->length})";
}







protected function typeString(Fluent $column)
{
return "nvarchar({$column->length})";
}







protected function typeTinyText(Fluent $column)
{
return 'nvarchar(255)';
}







protected function typeText(Fluent $column)
{
return 'nvarchar(max)';
}







protected function typeMediumText(Fluent $column)
{
return 'nvarchar(max)';
}







protected function typeLongText(Fluent $column)
{
return 'nvarchar(max)';
}







protected function typeInteger(Fluent $column)
{
return 'int';
}







protected function typeBigInteger(Fluent $column)
{
return 'bigint';
}







protected function typeMediumInteger(Fluent $column)
{
return 'int';
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
return 'double precision';
}







protected function typeDecimal(Fluent $column)
{
return "decimal({$column->total}, {$column->places})";
}







protected function typeBoolean(Fluent $column)
{
return 'bit';
}







protected function typeEnum(Fluent $column)
{
return sprintf(
'nvarchar(255) check ("%s" in (%s))',
$column->name,
$this->quoteString($column->allowed)
);
}







protected function typeJson(Fluent $column)
{
return 'nvarchar(max)';
}







protected function typeJsonb(Fluent $column)
{
return 'nvarchar(max)';
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
return $this->typeTimestampTz($column);
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
if ($column->useCurrent) {
$column->default(new Expression('CURRENT_TIMESTAMP'));
}

return $column->precision ? "datetime2($column->precision)" : 'datetime';
}









protected function typeTimestampTz(Fluent $column)
{
if ($column->useCurrent) {
$column->default(new Expression('CURRENT_TIMESTAMP'));
}

return $column->precision ? "datetimeoffset($column->precision)" : 'datetimeoffset';
}







protected function typeYear(Fluent $column)
{
return $this->typeInteger($column);
}







protected function typeBinary(Fluent $column)
{
if ($column->length) {
return $column->fixed ? "binary({$column->length})" : "varbinary({$column->length})";
}

return 'varbinary(max)';
}







protected function typeUuid(Fluent $column)
{
return 'uniqueidentifier';
}







protected function typeIpAddress(Fluent $column)
{
return 'nvarchar(45)';
}







protected function typeMacAddress(Fluent $column)
{
return 'nvarchar(17)';
}







protected function typeGeometry(Fluent $column)
{
return 'geometry';
}







protected function typeGeography(Fluent $column)
{
return 'geography';
}







protected function typeComputed(Fluent $column)
{
return "as ({$this->getValue($column->expression)})";
}








protected function modifyCollate(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->collation)) {
return ' collate '.$column->collation;
}
}








protected function modifyNullable(Blueprint $blueprint, Fluent $column)
{
if ($column->type !== 'computed') {
return $column->nullable ? ' null' : ' not null';
}
}








protected function modifyDefault(Blueprint $blueprint, Fluent $column)
{
if (! $column->change && ! is_null($column->default)) {
return ' default '.$this->getDefaultValue($column->default);
}
}








protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
{
if (! $column->change && in_array($column->type, $this->serials) && $column->autoIncrement) {
return $this->hasCommand($blueprint, 'primary') ? ' identity' : ' identity primary key';
}
}








protected function modifyPersisted(Blueprint $blueprint, Fluent $column)
{
if ($column->change) {
if ($column->type === 'computed') {
return $column->persisted ? ' add persisted' : ' drop persisted';
}

return null;
}

if ($column->persisted) {
return ' persisted';
}
}







public function wrapTable($table)
{
if ($table instanceof Blueprint && $table->temporary) {
$this->setTablePrefix('#');
}

return parent::wrapTable($table);
}







public function quoteString($value)
{
if (is_array($value)) {
return implode(', ', array_map([$this, __FUNCTION__], $value));
}

return "N'$value'";
}
}
