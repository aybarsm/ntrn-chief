<?php

namespace Illuminate\Database\Schema\Grammars;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use LogicException;

class PostgresGrammar extends Grammar
{





protected $transactions = true;






protected $modifiers = ['Collate', 'Nullable', 'Default', 'VirtualAs', 'StoredAs', 'GeneratedAs', 'Increment'];






protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];






protected $fluentCommands = ['AutoIncrementStartingValues', 'Comment'];








public function compileCreateDatabase($name, $connection)
{
return sprintf(
'create database %s encoding %s',
$this->wrapValue($name),
$this->wrapValue($connection->getConfig('charset')),
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
'select exists (select 1 from pg_class c, pg_namespace n where '
."n.nspname = %s and c.relname = %s and c.relkind in ('r', 'p') and n.oid = c.relnamespace)",
$this->quoteString($schema),
$this->quoteString($table)
);
}






public function compileTables()
{
return 'select c.relname as name, n.nspname as schema, pg_total_relation_size(c.oid) as size, '
."obj_description(c.oid, 'pg_class') as comment from pg_class c, pg_namespace n "
."where c.relkind in ('r', 'p') and n.oid = c.relnamespace and n.nspname not in ('pg_catalog', 'information_schema') "
.'order by c.relname';
}






public function compileViews()
{
return "select viewname as name, schemaname as schema, definition from pg_views where schemaname not in ('pg_catalog', 'information_schema') order by viewname";
}






public function compileTypes()
{
return 'select t.typname as name, n.nspname as schema, t.typtype as type, t.typcategory as category, '
."((t.typinput = 'array_in'::regproc and t.typoutput = 'array_out'::regproc) or t.typtype = 'm') as implicit "
.'from pg_type t join pg_namespace n on n.oid = t.typnamespace '
.'left join pg_class c on c.oid = t.typrelid '
.'left join pg_type el on el.oid = t.typelem '
.'left join pg_class ce on ce.oid = el.typrelid '
."where ((t.typrelid = 0 and (ce.relkind = 'c' or ce.relkind is null)) or c.relkind = 'c') "
."and not exists (select 1 from pg_depend d where d.objid in (t.oid, t.typelem) and d.deptype = 'e') "
."and n.nspname not in ('pg_catalog', 'information_schema')";
}








public function compileColumns($schema, $table)
{
return sprintf(
'select a.attname as name, t.typname as type_name, format_type(a.atttypid, a.atttypmod) as type, '
.'(select tc.collcollate from pg_catalog.pg_collation tc where tc.oid = a.attcollation) as collation, '
.'not a.attnotnull as nullable, '
.'(select pg_get_expr(adbin, adrelid) from pg_attrdef where c.oid = pg_attrdef.adrelid and pg_attrdef.adnum = a.attnum) as default, '
.(version_compare($this->connection?->getServerVersion(), '12.0', '<') ? "'' as generated, " : 'a.attgenerated as generated, ')
.'col_description(c.oid, a.attnum) as comment '
.'from pg_attribute a, pg_class c, pg_type t, pg_namespace n '
.'where c.relname = %s and n.nspname = %s and a.attnum > 0 and a.attrelid = c.oid and a.atttypid = t.oid and n.oid = c.relnamespace '
.'order by a.attnum',
$this->quoteString($table),
$this->quoteString($schema)
);
}








public function compileIndexes($schema, $table)
{
return sprintf(
"select ic.relname as name, string_agg(a.attname, ',' order by indseq.ord) as columns, "
.'am.amname as "type", i.indisunique as "unique", i.indisprimary as "primary" '
.'from pg_index i '
.'join pg_class tc on tc.oid = i.indrelid '
.'join pg_namespace tn on tn.oid = tc.relnamespace '
.'join pg_class ic on ic.oid = i.indexrelid '
.'join pg_am am on am.oid = ic.relam '
.'join lateral unnest(i.indkey) with ordinality as indseq(num, ord) on true '
.'left join pg_attribute a on a.attrelid = i.indrelid and a.attnum = indseq.num '
.'where tc.relname = %s and tn.nspname = %s '
.'group by ic.relname, am.amname, i.indisunique, i.indisprimary',
$this->quoteString($table),
$this->quoteString($schema)
);
}








public function compileForeignKeys($schema, $table)
{
return sprintf(
'select c.conname as name, '
."string_agg(la.attname, ',' order by conseq.ord) as columns, "
.'fn.nspname as foreign_schema, fc.relname as foreign_table, '
."string_agg(fa.attname, ',' order by conseq.ord) as foreign_columns, "
.'c.confupdtype as on_update, c.confdeltype as on_delete '
.'from pg_constraint c '
.'join pg_class tc on c.conrelid = tc.oid '
.'join pg_namespace tn on tn.oid = tc.relnamespace '
.'join pg_class fc on c.confrelid = fc.oid '
.'join pg_namespace fn on fn.oid = fc.relnamespace '
.'join lateral unnest(c.conkey) with ordinality as conseq(num, ord) on true '
.'join pg_attribute la on la.attrelid = c.conrelid and la.attnum = conseq.num '
.'join pg_attribute fa on fa.attrelid = c.confrelid and fa.attnum = c.confkey[conseq.ord] '
."where c.contype = 'f' and tc.relname = %s and tn.nspname = %s "
.'group by c.conname, fn.nspname, fc.relname, c.confupdtype, c.confdeltype',
$this->quoteString($table),
$this->quoteString($schema)
);
}








public function compileCreate(Blueprint $blueprint, Fluent $command)
{
return sprintf('%s table %s (%s)',
$blueprint->temporary ? 'create temporary' : 'create',
$this->wrapTable($blueprint),
implode(', ', $this->getColumns($blueprint))
);
}








public function compileAdd(Blueprint $blueprint, Fluent $command)
{
return sprintf('alter table %s add column %s',
$this->wrapTable($blueprint),
$this->getColumn($blueprint, $command->column)
);
}








public function compileAutoIncrementStartingValues(Blueprint $blueprint, Fluent $command)
{
if ($command->column->autoIncrement
&& $value = $command->column->get('startingValue', $command->column->get('from'))) {
$table = last(explode('.', $blueprint->getTable()));

return 'alter sequence '.$blueprint->getPrefix().$table.'_'.$command->column->name.'_seq restart with '.$value;
}
}











public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection)
{
$column = $command->column;

$changes = ['type '.$this->getType($column).$this->modifyCollate($blueprint, $column)];

foreach ($this->modifiers as $modifier) {
if ($modifier === 'Collate') {
continue;
}

if (method_exists($this, $method = "modify{$modifier}")) {
$constraints = (array) $this->{$method}($blueprint, $column);

foreach ($constraints as $constraint) {
$changes[] = $constraint;
}
}
}

return sprintf('alter table %s %s',
$this->wrapTable($blueprint),
implode(', ', $this->prefixArray('alter column '.$this->wrap($column), $changes))
);
}








public function compilePrimary(Blueprint $blueprint, Fluent $command)
{
$columns = $this->columnize($command->columns);

return 'alter table '.$this->wrapTable($blueprint)." add primary key ({$columns})";
}








public function compileUnique(Blueprint $blueprint, Fluent $command)
{
$sql = sprintf('alter table %s add constraint %s unique (%s)',
$this->wrapTable($blueprint),
$this->wrap($command->index),
$this->columnize($command->columns)
);

if (! is_null($command->deferrable)) {
$sql .= $command->deferrable ? ' deferrable' : ' not deferrable';
}

if ($command->deferrable && ! is_null($command->initiallyImmediate)) {
$sql .= $command->initiallyImmediate ? ' initially immediate' : ' initially deferred';
}

return $sql;
}








public function compileIndex(Blueprint $blueprint, Fluent $command)
{
return sprintf('create index %s on %s%s (%s)',
$this->wrap($command->index),
$this->wrapTable($blueprint),
$command->algorithm ? ' using '.$command->algorithm : '',
$this->columnize($command->columns)
);
}










public function compileFulltext(Blueprint $blueprint, Fluent $command)
{
$language = $command->language ?: 'english';

$columns = array_map(function ($column) use ($language) {
return "to_tsvector({$this->quoteString($language)}, {$this->wrap($column)})";
}, $command->columns);

return sprintf('create index %s on %s using gin ((%s))',
$this->wrap($command->index),
$this->wrapTable($blueprint),
implode(' || ', $columns)
);
}








public function compileSpatialIndex(Blueprint $blueprint, Fluent $command)
{
$command->algorithm = 'gist';

return $this->compileIndex($blueprint, $command);
}








public function compileForeign(Blueprint $blueprint, Fluent $command)
{
$sql = parent::compileForeign($blueprint, $command);

if (! is_null($command->deferrable)) {
$sql .= $command->deferrable ? ' deferrable' : ' not deferrable';
}

if ($command->deferrable && ! is_null($command->initiallyImmediate)) {
$sql .= $command->initiallyImmediate ? ' initially immediate' : ' initially deferred';
}

if (! is_null($command->notValid)) {
$sql .= ' not valid';
}

return $sql;
}








public function compileDrop(Blueprint $blueprint, Fluent $command)
{
return 'drop table '.$this->wrapTable($blueprint);
}








public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
{
return 'drop table if exists '.$this->wrapTable($blueprint);
}







public function compileDropAllTables($tables)
{
return 'drop table '.implode(',', $this->escapeNames($tables)).' cascade';
}







public function compileDropAllViews($views)
{
return 'drop view '.implode(',', $this->escapeNames($views)).' cascade';
}







public function compileDropAllTypes($types)
{
return 'drop type '.implode(',', $this->escapeNames($types)).' cascade';
}







public function compileDropAllDomains($domains)
{
return 'drop domain '.implode(',', $this->escapeNames($domains)).' cascade';
}








public function compileDropColumn(Blueprint $blueprint, Fluent $command)
{
$columns = $this->prefixArray('drop column', $this->wrapArray($command->columns));

return 'alter table '.$this->wrapTable($blueprint).' '.implode(', ', $columns);
}








public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
{
$table = last(explode('.', $blueprint->getTable()));
$index = $this->wrap("{$blueprint->getPrefix()}{$table}_pkey");

return 'alter table '.$this->wrapTable($blueprint)." drop constraint {$index}";
}








public function compileDropUnique(Blueprint $blueprint, Fluent $command)
{
$index = $this->wrap($command->index);

return "alter table {$this->wrapTable($blueprint)} drop constraint {$index}";
}








public function compileDropIndex(Blueprint $blueprint, Fluent $command)
{
return "drop index {$this->wrap($command->index)}";
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

return "alter table {$this->wrapTable($blueprint)} drop constraint {$index}";
}








public function compileRename(Blueprint $blueprint, Fluent $command)
{
$from = $this->wrapTable($blueprint);

return "alter table {$from} rename to ".$this->wrapTable($command->to);
}








public function compileRenameIndex(Blueprint $blueprint, Fluent $command)
{
return sprintf('alter index %s rename to %s',
$this->wrap($command->from),
$this->wrap($command->to)
);
}






public function compileEnableForeignKeyConstraints()
{
return 'SET CONSTRAINTS ALL IMMEDIATE;';
}






public function compileDisableForeignKeyConstraints()
{
return 'SET CONSTRAINTS ALL DEFERRED;';
}








public function compileComment(Blueprint $blueprint, Fluent $command)
{
if (! is_null($comment = $command->column->comment) || $command->column->change) {
return sprintf('comment on column %s.%s is %s',
$this->wrapTable($blueprint),
$this->wrap($command->column->name),
is_null($comment) ? 'NULL' : "'".str_replace("'", "''", $comment)."'"
);
}
}








public function compileTableComment(Blueprint $blueprint, Fluent $command)
{
return sprintf('comment on table %s is %s',
$this->wrapTable($blueprint),
"'".str_replace("'", "''", $command->comment)."'"
);
}







public function escapeNames($names)
{
return array_map(static function ($name) {
return '"'.collect(explode('.', $name))
->map(fn ($segment) => trim($segment, '\'"'))
->implode('"."').'"';
}, $names);
}







protected function typeChar(Fluent $column)
{
if ($column->length) {
return "char({$column->length})";
}

return 'char';
}







protected function typeString(Fluent $column)
{
if ($column->length) {
return "varchar({$column->length})";
}

return 'varchar';
}







protected function typeTinyText(Fluent $column)
{
return 'varchar(255)';
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
return $column->autoIncrement && is_null($column->generatedAs) && ! $column->change ? 'serial' : 'integer';
}







protected function typeBigInteger(Fluent $column)
{
return $column->autoIncrement && is_null($column->generatedAs) && ! $column->change ? 'bigserial' : 'bigint';
}







protected function typeMediumInteger(Fluent $column)
{
return $this->typeInteger($column);
}







protected function typeTinyInteger(Fluent $column)
{
return $this->typeSmallInteger($column);
}







protected function typeSmallInteger(Fluent $column)
{
return $column->autoIncrement && is_null($column->generatedAs) && ! $column->change ? 'smallserial' : 'smallint';
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







protected function typeReal(Fluent $column)
{
return 'real';
}







protected function typeDecimal(Fluent $column)
{
return "decimal({$column->total}, {$column->places})";
}







protected function typeBoolean(Fluent $column)
{
return 'boolean';
}







protected function typeEnum(Fluent $column)
{
return sprintf(
'varchar(255) check ("%s" in (%s))',
$column->name,
$this->quoteString($column->allowed)
);
}







protected function typeJson(Fluent $column)
{
return 'json';
}







protected function typeJsonb(Fluent $column)
{
return 'jsonb';
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
return 'time'.(is_null($column->precision) ? '' : "($column->precision)").' without time zone';
}







protected function typeTimeTz(Fluent $column)
{
return 'time'.(is_null($column->precision) ? '' : "($column->precision)").' with time zone';
}







protected function typeTimestamp(Fluent $column)
{
if ($column->useCurrent) {
$column->default(new Expression('CURRENT_TIMESTAMP'));
}

return 'timestamp'.(is_null($column->precision) ? '' : "($column->precision)").' without time zone';
}







protected function typeTimestampTz(Fluent $column)
{
if ($column->useCurrent) {
$column->default(new Expression('CURRENT_TIMESTAMP'));
}

return 'timestamp'.(is_null($column->precision) ? '' : "($column->precision)").' with time zone';
}







protected function typeYear(Fluent $column)
{
return $this->typeInteger($column);
}







protected function typeBinary(Fluent $column)
{
return 'bytea';
}







protected function typeUuid(Fluent $column)
{
return 'uuid';
}







protected function typeIpAddress(Fluent $column)
{
return 'inet';
}







protected function typeMacAddress(Fluent $column)
{
return 'macaddr';
}







protected function typeGeometry(Fluent $column)
{
if ($column->subtype) {
return sprintf('geometry(%s%s)',
strtolower($column->subtype),
$column->srid ? ','.$column->srid : ''
);
}

return 'geometry';
}







protected function typeGeography(Fluent $column)
{
if ($column->subtype) {
return sprintf('geography(%s%s)',
strtolower($column->subtype),
$column->srid ? ','.$column->srid : ''
);
}

return 'geography';
}







protected function typeVector(Fluent $column)
{
return "vector($column->dimensions)";
}








protected function modifyCollate(Blueprint $blueprint, Fluent $column)
{
if (! is_null($column->collation)) {
return ' collate '.$this->wrapValue($column->collation);
}
}








protected function modifyNullable(Blueprint $blueprint, Fluent $column)
{
if ($column->change) {
return $column->nullable ? 'drop not null' : 'set not null';
}

return $column->nullable ? ' null' : ' not null';
}








protected function modifyDefault(Blueprint $blueprint, Fluent $column)
{
if ($column->change) {
if (! $column->autoIncrement || ! is_null($column->generatedAs)) {
return is_null($column->default) ? 'drop default' : 'set default '.$this->getDefaultValue($column->default);
}

return null;
}

if (! is_null($column->default)) {
return ' default '.$this->getDefaultValue($column->default);
}
}








protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
{
if (! $column->change
&& ! $this->hasCommand($blueprint, 'primary')
&& (in_array($column->type, $this->serials) || ($column->generatedAs !== null))
&& $column->autoIncrement) {
return ' primary key';
}
}








protected function modifyVirtualAs(Blueprint $blueprint, Fluent $column)
{
if ($column->change) {
if (array_key_exists('virtualAs', $column->getAttributes())) {
return is_null($column->virtualAs)
? 'drop expression if exists'
: throw new LogicException('This database driver does not support modifying generated columns.');
}

return null;
}

if (! is_null($column->virtualAs)) {
return " generated always as ({$this->getValue($column->virtualAs)})";
}
}








protected function modifyStoredAs(Blueprint $blueprint, Fluent $column)
{
if ($column->change) {
if (array_key_exists('storedAs', $column->getAttributes())) {
return is_null($column->storedAs)
? 'drop expression if exists'
: throw new LogicException('This database driver does not support modifying generated columns.');
}

return null;
}

if (! is_null($column->storedAs)) {
return " generated always as ({$this->getValue($column->storedAs)}) stored";
}
}








protected function modifyGeneratedAs(Blueprint $blueprint, Fluent $column)
{
$sql = null;

if (! is_null($column->generatedAs)) {
$sql = sprintf(
' generated %s as identity%s',
$column->always ? 'always' : 'by default',
! is_bool($column->generatedAs) && ! empty($column->generatedAs) ? " ({$column->generatedAs})" : ''
);
}

if ($column->change) {
$changes = $column->autoIncrement && is_null($sql) ? [] : ['drop identity if exists'];

if (! is_null($sql)) {
$changes[] = 'add '.$sql;
}

return $changes;
}

return $sql;
}
}
