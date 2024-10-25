<?php

namespace Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinLateralClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PostgresGrammar extends Grammar
{





protected $operators = [
'=', '<', '>', '<=', '>=', '<>', '!=',
'like', 'not like', 'between', 'ilike', 'not ilike',
'~', '&', '|', '#', '<<', '>>', '<<=', '>>=',
'&&', '@>', '<@', '?', '?|', '?&', '||', '-', '@?', '@@', '#-',
'is distinct from', 'is not distinct from',
];






protected $bitwiseOperators = [
'~', '&', '|', '#', '<<', '>>', '<<=', '>>=',
];








protected function whereBasic(Builder $query, $where)
{
if (str_contains(strtolower($where['operator']), 'like')) {
return sprintf(
'%s::text %s %s',
$this->wrap($where['column']),
$where['operator'],
$this->parameter($where['value'])
);
}

return parent::whereBasic($query, $where);
}








protected function whereBitwise(Builder $query, $where)
{
$value = $this->parameter($where['value']);

$operator = str_replace('?', '??', $where['operator']);

return '('.$this->wrap($where['column']).' '.$operator.' '.$value.')::bool';
}








protected function whereLike(Builder $query, $where)
{
$where['operator'] = $where['not'] ? 'not ' : '';

$where['operator'] .= $where['caseSensitive'] ? 'like' : 'ilike';

return $this->whereBasic($query, $where);
}








protected function whereDate(Builder $query, $where)
{
$value = $this->parameter($where['value']);

return $this->wrap($where['column']).'::date '.$where['operator'].' '.$value;
}








protected function whereTime(Builder $query, $where)
{
$value = $this->parameter($where['value']);

return $this->wrap($where['column']).'::time '.$where['operator'].' '.$value;
}









protected function dateBasedWhere($type, Builder $query, $where)
{
$value = $this->parameter($where['value']);

return 'extract('.$type.' from '.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
}








public function whereFullText(Builder $query, $where)
{
$language = $where['options']['language'] ?? 'english';

if (! in_array($language, $this->validFullTextLanguages())) {
$language = 'english';
}

$columns = collect($where['columns'])->map(function ($column) use ($language) {
return "to_tsvector('{$language}', {$this->wrap($column)})";
})->implode(' || ');

$mode = 'plainto_tsquery';

if (($where['options']['mode'] ?? []) === 'phrase') {
$mode = 'phraseto_tsquery';
}

if (($where['options']['mode'] ?? []) === 'websearch') {
$mode = 'websearch_to_tsquery';
}

return "({$columns}) @@ {$mode}('{$language}', {$this->parameter($where['value'])})";
}






protected function validFullTextLanguages()
{
return [
'simple',
'arabic',
'danish',
'dutch',
'english',
'finnish',
'french',
'german',
'hungarian',
'indonesian',
'irish',
'italian',
'lithuanian',
'nepali',
'norwegian',
'portuguese',
'romanian',
'russian',
'spanish',
'swedish',
'tamil',
'turkish',
];
}








protected function compileColumns(Builder $query, $columns)
{



if (! is_null($query->aggregate)) {
return;
}

if (is_array($query->distinct)) {
$select = 'select distinct on ('.$this->columnize($query->distinct).') ';
} elseif ($query->distinct) {
$select = 'select distinct ';
} else {
$select = 'select ';
}

return $select.$this->columnize($columns);
}








protected function compileJsonContains($column, $value)
{
$column = str_replace('->>', '->', $this->wrap($column));

return '('.$column.')::jsonb @> '.$value;
}







protected function compileJsonContainsKey($column)
{
$segments = explode('->', $column);

$lastSegment = array_pop($segments);

if (filter_var($lastSegment, FILTER_VALIDATE_INT) !== false) {
$i = $lastSegment;
} elseif (preg_match('/\[(-?[0-9]+)\]$/', $lastSegment, $matches)) {
$segments[] = Str::beforeLast($lastSegment, $matches[0]);

$i = $matches[1];
}

$column = str_replace('->>', '->', $this->wrap(implode('->', $segments)));

if (isset($i)) {
return vsprintf('case when %s then %s else false end', [
'jsonb_typeof(('.$column.")::jsonb) = 'array'",
'jsonb_array_length(('.$column.')::jsonb) >= '.($i < 0 ? abs($i) : $i + 1),
]);
}

$key = "'".str_replace("'", "''", $lastSegment)."'";

return 'coalesce(('.$column.')::jsonb ?? '.$key.', false)';
}









protected function compileJsonLength($column, $operator, $value)
{
$column = str_replace('->>', '->', $this->wrap($column));

return 'jsonb_array_length(('.$column.')::jsonb) '.$operator.' '.$value;
}







protected function compileHaving(array $having)
{
if ($having['type'] === 'Bitwise') {
return $this->compileHavingBitwise($having);
}

return parent::compileHaving($having);
}







protected function compileHavingBitwise($having)
{
$column = $this->wrap($having['column']);

$parameter = $this->parameter($having['value']);

return '('.$column.' '.$having['operator'].' '.$parameter.')::bool';
}








protected function compileLock(Builder $query, $value)
{
if (! is_string($value)) {
return $value ? 'for update' : 'for share';
}

return $value;
}








public function compileInsertOrIgnore(Builder $query, array $values)
{
return $this->compileInsert($query, $values).' on conflict do nothing';
}









public function compileInsertOrIgnoreUsing(Builder $query, array $columns, string $sql)
{
return $this->compileInsertUsing($query, $columns, $sql).' on conflict do nothing';
}









public function compileInsertGetId(Builder $query, $values, $sequence)
{
return $this->compileInsert($query, $values).' returning '.$this->wrap($sequence ?: 'id');
}








public function compileUpdate(Builder $query, array $values)
{
if (isset($query->joins) || isset($query->limit)) {
return $this->compileUpdateWithJoinsOrLimit($query, $values);
}

return parent::compileUpdate($query, $values);
}








protected function compileUpdateColumns(Builder $query, array $values)
{
return collect($values)->map(function ($value, $key) {
$column = last(explode('.', $key));

if ($this->isJsonSelector($key)) {
return $this->compileJsonUpdateColumn($column, $value);
}

return $this->wrap($column).' = '.$this->parameter($value);
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








public function compileJoinLateral(JoinLateralClause $join, string $expression): string
{
return trim("{$join->type} join lateral {$expression} on true");
}








protected function compileJsonUpdateColumn($key, $value)
{
$segments = explode('->', $key);

$field = $this->wrap(array_shift($segments));

$path = "'{".implode(',', $this->wrapJsonPathAttributes($segments, '"'))."}'";

return "{$field} = jsonb_set({$field}::jsonb, {$path}, {$this->parameter($value)})";
}








public function compileUpdateFrom(Builder $query, $values)
{
$table = $this->wrapTable($query->from);




$columns = $this->compileUpdateColumns($query, $values);

$from = '';

if (isset($query->joins)) {



$froms = collect($query->joins)->map(function ($join) {
return $this->wrapTable($join->table);
})->all();

if (count($froms) > 0) {
$from = ' from '.implode(', ', $froms);
}
}

$where = $this->compileUpdateWheres($query);

return trim("update {$table} set {$columns}{$from} {$where}");
}







protected function compileUpdateWheres(Builder $query)
{
$baseWheres = $this->compileWheres($query);

if (! isset($query->joins)) {
return $baseWheres;
}




$joinWheres = $this->compileUpdateJoinWheres($query);

if (trim($baseWheres) == '') {
return 'where '.$this->removeLeadingBoolean($joinWheres);
}

return $baseWheres.' '.$joinWheres;
}







protected function compileUpdateJoinWheres(Builder $query)
{
$joinWheres = [];




foreach ($query->joins as $join) {
foreach ($join->wheres as $where) {
$method = "where{$where['type']}";

$joinWheres[] = $where['boolean'].' '.$this->$method($query, $where);
}
}

return implode(' ', $joinWheres);
}








public function prepareBindingsForUpdateFrom(array $bindings, array $values)
{
$values = collect($values)->map(function ($value, $column) {
return is_array($value) || ($this->isJsonSelector($column) && ! $this->isExpression($value))
? json_encode($value)
: $value;
})->all();

$bindingsWithoutWhere = Arr::except($bindings, ['select', 'where']);

return array_values(
array_merge($values, $bindings['where'], Arr::flatten($bindingsWithoutWhere))
);
}








protected function compileUpdateWithJoinsOrLimit(Builder $query, array $values)
{
$table = $this->wrapTable($query->from);

$columns = $this->compileUpdateColumns($query, $values);

$alias = last(preg_split('/\s+as\s+/i', $query->from));

$selectSql = $this->compileSelect($query->select($alias.'.ctid'));

return "update {$table} set {$columns} where {$this->wrap('ctid')} in ({$selectSql})";
}








public function prepareBindingsForUpdate(array $bindings, array $values)
{
$values = collect($values)->map(function ($value, $column) {
return is_array($value) || ($this->isJsonSelector($column) && ! $this->isExpression($value))
? json_encode($value)
: $value;
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

$selectSql = $this->compileSelect($query->select($alias.'.ctid'));

return "delete from {$table} where {$this->wrap('ctid')} in ({$selectSql})";
}







public function compileTruncate(Builder $query)
{
return ['truncate '.$this->wrapTable($query->from).' restart identity cascade' => []];
}






public function compileThreadCount()
{
return 'select count(*) as "Value" from pg_stat_activity';
}







protected function wrapJsonSelector($value)
{
$path = explode('->', $value);

$field = $this->wrapSegments(explode('.', array_shift($path)));

$wrappedPath = $this->wrapJsonPathAttributes($path);

$attribute = array_pop($wrappedPath);

if (! empty($wrappedPath)) {
return $field.'->'.implode('->', $wrappedPath).'->>'.$attribute;
}

return $field.'->>'.$attribute;
}







protected function wrapJsonBooleanSelector($value)
{
$selector = str_replace(
'->>', '->',
$this->wrapJsonSelector($value)
);

return '('.$selector.')::jsonb';
}







protected function wrapJsonBooleanValue($value)
{
return "'".$value."'::jsonb";
}







protected function wrapJsonPathAttributes($path)
{
$quote = func_num_args() === 2 ? func_get_arg(1) : "'";

return collect($path)->map(function ($attribute) {
return $this->parseJsonPathArrayKeys($attribute);
})->collapse()->map(function ($attribute) use ($quote) {
return filter_var($attribute, FILTER_VALIDATE_INT) !== false
? $attribute
: $quote.$attribute.$quote;
})->all();
}







protected function parseJsonPathArrayKeys($attribute)
{
if (preg_match('/(\[[^\]]+\])+$/', $attribute, $parts)) {
$key = Str::beforeLast($attribute, $parts[0]);

preg_match_all('/\[([^\]]+)\]/', $parts[0], $keys);

return collect([$key])
->merge($keys[1])
->diff('')
->values()
->all();
}

return [$attribute];
}








public function substituteBindingsIntoRawSql($sql, $bindings)
{
$query = parent::substituteBindingsIntoRawSql($sql, $bindings);

foreach ($this->operators as $operator) {
if (! str_contains($operator, '?')) {
continue;
}

$query = str_replace(str_replace('?', '??', $operator), $operator, $query);
}

return $query;
}
}
