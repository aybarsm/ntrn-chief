<?php

namespace Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinLateralClause;
use Illuminate\Support\Str;

class MySqlGrammar extends Grammar
{





protected $operators = ['sounds like'];








protected function whereLike(Builder $query, $where)
{
$where['operator'] = $where['not'] ? 'not ' : '';

$where['operator'] .= $where['caseSensitive'] ? 'like binary' : 'like';

return $this->whereBasic($query, $where);
}








protected function whereNull(Builder $query, $where)
{
$columnValue = (string) $this->getValue($where['column']);

if ($this->isJsonSelector($columnValue)) {
[$field, $path] = $this->wrapJsonFieldAndPath($columnValue);

return '(json_extract('.$field.$path.') is null OR json_type(json_extract('.$field.$path.')) = \'NULL\')';
}

return parent::whereNull($query, $where);
}








protected function whereNotNull(Builder $query, $where)
{
$columnValue = (string) $this->getValue($where['column']);

if ($this->isJsonSelector($columnValue)) {
[$field, $path] = $this->wrapJsonFieldAndPath($columnValue);

return '(json_extract('.$field.$path.') is not null AND json_type(json_extract('.$field.$path.')) != \'NULL\')';
}

return parent::whereNotNull($query, $where);
}








public function whereFullText(Builder $query, $where)
{
$columns = $this->columnize($where['columns']);

$value = $this->parameter($where['value']);

$mode = ($where['options']['mode'] ?? []) === 'boolean'
? ' in boolean mode'
: ' in natural language mode';

$expanded = ($where['options']['expanded'] ?? []) && ($where['options']['mode'] ?? []) !== 'boolean'
? ' with query expansion'
: '';

return "match ({$columns}) against (".$value."{$mode}{$expanded})";
}








protected function compileIndexHint(Builder $query, $indexHint)
{
return match ($indexHint->type) {
'hint' => "use index ({$indexHint->index})",
'force' => "force index ({$indexHint->index})",
default => "ignore index ({$indexHint->index})",
};
}







protected function compileGroupLimit(Builder $query)
{
return $this->useLegacyGroupLimit($query)
? $this->compileLegacyGroupLimit($query)
: parent::compileGroupLimit($query);
}







public function useLegacyGroupLimit(Builder $query)
{
$version = $query->getConnection()->getServerVersion();

return ! $query->getConnection()->isMaria() && version_compare($version, '8.0.11') < 0;
}









protected function compileLegacyGroupLimit(Builder $query)
{
$limit = (int) $query->groupLimit['value'];
$offset = $query->offset;

if (isset($offset)) {
$offset = (int) $offset;
$limit += $offset;

$query->offset = null;
}

$column = last(explode('.', $query->groupLimit['column']));
$column = $this->wrap($column);

$partition = ', @laravel_row := if(@laravel_group = '.$column.', @laravel_row + 1, 1) as `laravel_row`';
$partition .= ', @laravel_group := '.$column;

$orders = (array) $query->orders;

array_unshift($orders, [
'column' => $query->groupLimit['column'],
'direction' => 'asc',
]);

$query->orders = $orders;

$components = $this->compileComponents($query);

$sql = $this->concatenate($components);

$from = '(select @laravel_row := 0, @laravel_group := 0) as `laravel_vars`, ('.$sql.') as `laravel_table`';

$sql = 'select `laravel_table`.*'.$partition.' from '.$from.' having `laravel_row` <= '.$limit;

if (isset($offset)) {
$sql .= ' and `laravel_row` > '.$offset;
}

return $sql.' order by `laravel_row`';
}








public function compileInsertOrIgnore(Builder $query, array $values)
{
return Str::replaceFirst('insert', 'insert ignore', $this->compileInsert($query, $values));
}









public function compileInsertOrIgnoreUsing(Builder $query, array $columns, string $sql)
{
return Str::replaceFirst('insert', 'insert ignore', $this->compileInsertUsing($query, $columns, $sql));
}








protected function compileJsonContains($column, $value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($column);

return 'json_contains('.$field.', '.$value.$path.')';
}








protected function compileJsonOverlaps($column, $value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($column);

return 'json_overlaps('.$field.', '.$value.$path.')';
}







protected function compileJsonContainsKey($column)
{
[$field, $path] = $this->wrapJsonFieldAndPath($column);

return 'ifnull(json_contains_path('.$field.', \'one\''.$path.'), 0)';
}









protected function compileJsonLength($column, $operator, $value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($column);

return 'json_length('.$field.$path.') '.$operator.' '.$value;
}







public function compileJsonValueCast($value)
{
return 'cast('.$value.' as json)';
}







public function compileRandom($seed)
{
return 'RAND('.$seed.')';
}








protected function compileLock(Builder $query, $value)
{
if (! is_string($value)) {
return $value ? 'for update' : 'lock in share mode';
}

return $value;
}








public function compileInsert(Builder $query, array $values)
{
if (empty($values)) {
$values = [[]];
}

return parent::compileInsert($query, $values);
}








protected function compileUpdateColumns(Builder $query, array $values)
{
return collect($values)->map(function ($value, $key) {
if ($this->isJsonSelector($key)) {
return $this->compileJsonUpdateColumn($key, $value);
}

return $this->wrap($key).' = '.$this->parameter($value);
})->implode(', ');
}










public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update)
{
$useUpsertAlias = $query->connection->getConfig('use_upsert_alias');

$sql = $this->compileInsert($query, $values);

if ($useUpsertAlias) {
$sql .= ' as laravel_upsert_alias';
}

$sql .= ' on duplicate key update ';

$columns = collect($update)->map(function ($value, $key) use ($useUpsertAlias) {
if (! is_numeric($key)) {
return $this->wrap($key).' = '.$this->parameter($value);
}

return $useUpsertAlias
? $this->wrap($value).' = '.$this->wrap('laravel_upsert_alias').'.'.$this->wrap($value)
: $this->wrap($value).' = values('.$this->wrap($value).')';
})->implode(', ');

return $sql.$columns;
}








public function compileJoinLateral(JoinLateralClause $join, string $expression): string
{
return trim("{$join->type} join lateral {$expression} on true");
}








protected function compileJsonUpdateColumn($key, $value)
{
if (is_bool($value)) {
$value = $value ? 'true' : 'false';
} elseif (is_array($value)) {
$value = 'cast(? as json)';
} else {
$value = $this->parameter($value);
}

[$field, $path] = $this->wrapJsonFieldAndPath($key);

return "{$field} = json_set({$field}{$path}, {$value})";
}










protected function compileUpdateWithoutJoins(Builder $query, $table, $columns, $where)
{
$sql = parent::compileUpdateWithoutJoins($query, $table, $columns, $where);

if (! empty($query->orders)) {
$sql .= ' '.$this->compileOrders($query, $query->orders);
}

if (isset($query->limit)) {
$sql .= ' '.$this->compileLimit($query, $query->limit);
}

return $sql;
}










public function prepareBindingsForUpdate(array $bindings, array $values)
{
$values = collect($values)->reject(function ($value, $column) {
return $this->isJsonSelector($column) && is_bool($value);
})->map(function ($value) {
return is_array($value) ? json_encode($value) : $value;
})->all();

return parent::prepareBindingsForUpdate($bindings, $values);
}









protected function compileDeleteWithoutJoins(Builder $query, $table, $where)
{
$sql = parent::compileDeleteWithoutJoins($query, $table, $where);




if (! empty($query->orders)) {
$sql .= ' '.$this->compileOrders($query, $query->orders);
}

if (isset($query->limit)) {
$sql .= ' '.$this->compileLimit($query, $query->limit);
}

return $sql;
}






public function compileThreadCount()
{
return 'select variable_value as `Value` from performance_schema.session_status where variable_name = \'threads_connected\'';
}







protected function wrapValue($value)
{
return $value === '*' ? $value : '`'.str_replace('`', '``', $value).'`';
}







protected function wrapJsonSelector($value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($value);

return 'json_unquote(json_extract('.$field.$path.'))';
}







protected function wrapJsonBooleanSelector($value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($value);

return 'json_extract('.$field.$path.')';
}
}
