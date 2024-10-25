<?php

namespace Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SQLiteGrammar extends Grammar
{





protected $operators = [
'=', '<', '>', '<=', '>=', '<>', '!=',
'like', 'not like', 'ilike',
'&', '|', '<<', '>>',
];








protected function compileLock(Builder $query, $value)
{
return '';
}







protected function wrapUnion($sql)
{
return 'select * from ('.$sql.')';
}








protected function whereLike(Builder $query, $where)
{
if ($where['caseSensitive'] == false) {
return parent::whereLike($query, $where);
}
$where['operator'] = $where['not'] ? 'not glob' : 'glob';

return $this->whereBasic($query, $where);
}








public function prepareWhereLikeBinding($value, $caseSensitive)
{
return $caseSensitive === false ? $value : str_replace(
['*', '?', '%', '_'],
['[*]', '[?]', '*', '?'],
$value
);
}








protected function whereDate(Builder $query, $where)
{
return $this->dateBasedWhere('%Y-%m-%d', $query, $where);
}








protected function whereDay(Builder $query, $where)
{
return $this->dateBasedWhere('%d', $query, $where);
}








protected function whereMonth(Builder $query, $where)
{
return $this->dateBasedWhere('%m', $query, $where);
}








protected function whereYear(Builder $query, $where)
{
return $this->dateBasedWhere('%Y', $query, $where);
}








protected function whereTime(Builder $query, $where)
{
return $this->dateBasedWhere('%H:%M:%S', $query, $where);
}









protected function dateBasedWhere($type, Builder $query, $where)
{
$value = $this->parameter($where['value']);

return "strftime('{$type}', {$this->wrap($where['column'])}) {$where['operator']} cast({$value} as text)";
}








protected function compileIndexHint(Builder $query, $indexHint)
{
return $indexHint->type === 'force'
? "indexed by {$indexHint->index}"
: '';
}









protected function compileJsonLength($column, $operator, $value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($column);

return 'json_array_length('.$field.$path.') '.$operator.' '.$value;
}








protected function compileJsonContains($column, $value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($column);

return 'exists (select 1 from json_each('.$field.$path.') where '.$this->wrap('json_each.value').' is '.$value.')';
}







public function prepareBindingForJsonContains($binding)
{
return $binding;
}







protected function compileJsonContainsKey($column)
{
[$field, $path] = $this->wrapJsonFieldAndPath($column);

return 'json_type('.$field.$path.') is not null';
}







protected function compileGroupLimit(Builder $query)
{
$version = $query->getConnection()->getServerVersion();

if (version_compare($version, '3.25.0') >= 0) {
return parent::compileGroupLimit($query);
}

$query->groupLimit = null;

return $this->compileSelect($query);
}








public function compileUpdate(Builder $query, array $values)
{
if (isset($query->joins) || isset($query->limit)) {
return $this->compileUpdateWithJoinsOrLimit($query, $values);
}

return parent::compileUpdate($query, $values);
}








public function compileInsertOrIgnore(Builder $query, array $values)
{
return Str::replaceFirst('insert', 'insert or ignore', $this->compileInsert($query, $values));
}









public function compileInsertOrIgnoreUsing(Builder $query, array $columns, string $sql)
{
return Str::replaceFirst('insert', 'insert or ignore', $this->compileInsertUsing($query, $columns, $sql));
}








protected function compileUpdateColumns(Builder $query, array $values)
{
$jsonGroups = $this->groupJsonColumnsForUpdate($values);

return collect($values)->reject(function ($value, $key) {
return $this->isJsonSelector($key);
})->merge($jsonGroups)->map(function ($value, $key) use ($jsonGroups) {
$column = last(explode('.', $key));

$value = isset($jsonGroups[$key]) ? $this->compileJsonPatch($column, $value) : $this->parameter($value);

return $this->wrap($column).' = '.$value;
})->implode(', ');
}










public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update)
{
$sql = $this->compileInsert($query, $values);

$sql .= ' on conflict ('.$this->columnize($uniqueBy).') do update set ';

$columns = collect($update)->map(function ($value, $key) {
return is_numeric($key)
? $this->wrap($value).' = '.$this->wrapValue('excluded').'.'.$this->wrap($value)
: $this->wrap($key).' = '.$this->parameter($value);
})->implode(', ');

return $sql.$columns;
}







protected function groupJsonColumnsForUpdate(array $values)
{
$groups = [];

foreach ($values as $key => $value) {
if ($this->isJsonSelector($key)) {
Arr::set($groups, str_replace('->', '.', Str::after($key, '.')), $value);
}
}

return $groups;
}








protected function compileJsonPatch($column, $value)
{
return "json_patch(ifnull({$this->wrap($column)}, json('{}')), json({$this->parameter($value)}))";
}








protected function compileUpdateWithJoinsOrLimit(Builder $query, array $values)
{
$table = $this->wrapTable($query->from);

$columns = $this->compileUpdateColumns($query, $values);

$alias = last(preg_split('/\s+as\s+/i', $query->from));

$selectSql = $this->compileSelect($query->select($alias.'.rowid'));

return "update {$table} set {$columns} where {$this->wrap('rowid')} in ({$selectSql})";
}








public function prepareBindingsForUpdate(array $bindings, array $values)
{
$groups = $this->groupJsonColumnsForUpdate($values);

$values = collect($values)->reject(function ($value, $key) {
return $this->isJsonSelector($key);
})->merge($groups)->map(function ($value) {
return is_array($value) ? json_encode($value) : $value;
})->all();

$cleanBindings = Arr::except($bindings, 'select');

return array_values(
array_merge($values, Arr::flatten($cleanBindings))
);
}







public function compileDelete(Builder $query)
{
if (isset($query->joins) || isset($query->limit)) {
return $this->compileDeleteWithJoinsOrLimit($query);
}

return parent::compileDelete($query);
}







protected function compileDeleteWithJoinsOrLimit(Builder $query)
{
$table = $this->wrapTable($query->from);

$alias = last(preg_split('/\s+as\s+/i', $query->from));

$selectSql = $this->compileSelect($query->select($alias.'.rowid'));

return "delete from {$table} where {$this->wrap('rowid')} in ({$selectSql})";
}







public function compileTruncate(Builder $query)
{
return [
'delete from sqlite_sequence where name = ?' => [$this->getTablePrefix().$query->from],
'delete from '.$this->wrapTable($query->from) => [],
];
}







protected function wrapJsonSelector($value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($value);

return 'json_extract('.$field.$path.')';
}
}
