<?php

namespace Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinLateralClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SqlServerGrammar extends Grammar
{





protected $operators = [
'=', '<', '>', '<=', '>=', '!<', '!>', '<>', '!=',
'like', 'not like', 'ilike',
'&', '&=', '|', '|=', '^', '^=',
];






protected $selectComponents = [
'aggregate',
'columns',
'from',
'indexHint',
'joins',
'wheres',
'groups',
'havings',
'orders',
'offset',
'limit',
'lock',
];







public function compileSelect(Builder $query)
{

if ($query->offset && empty($query->orders)) {
$query->orders[] = ['sql' => '(SELECT 0)'];
}

return parent::compileSelect($query);
}








protected function compileColumns(Builder $query, $columns)
{
if (! is_null($query->aggregate)) {
return;
}

$select = $query->distinct ? 'select distinct ' : 'select ';




if (is_numeric($query->limit) && $query->limit > 0 && $query->offset <= 0) {
$select .= 'top '.((int) $query->limit).' ';
}

return $select.$this->columnize($columns);
}








protected function compileFrom(Builder $query, $table)
{
$from = parent::compileFrom($query, $table);

if (is_string($query->lock)) {
return $from.' '.$query->lock;
}

if (! is_null($query->lock)) {
return $from.' with(rowlock,'.($query->lock ? 'updlock,' : '').'holdlock)';
}

return $from;
}








protected function compileIndexHint(Builder $query, $indexHint)
{
return $indexHint->type === 'force'
? "with (index({$indexHint->index}))"
: '';
}








protected function whereBitwise(Builder $query, $where)
{
$value = $this->parameter($where['value']);

$operator = str_replace('?', '??', $where['operator']);

return '('.$this->wrap($where['column']).' '.$operator.' '.$value.') != 0';
}








protected function whereDate(Builder $query, $where)
{
$value = $this->parameter($where['value']);

return 'cast('.$this->wrap($where['column']).' as date) '.$where['operator'].' '.$value;
}








protected function whereTime(Builder $query, $where)
{
$value = $this->parameter($where['value']);

return 'cast('.$this->wrap($where['column']).' as time) '.$where['operator'].' '.$value;
}








protected function compileJsonContains($column, $value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($column);

return $value.' in (select [value] from openjson('.$field.$path.'))';
}







public function prepareBindingForJsonContains($binding)
{
return is_bool($binding) ? json_encode($binding) : $binding;
}







protected function compileJsonContainsKey($column)
{
$segments = explode('->', $column);

$lastSegment = array_pop($segments);

if (preg_match('/\[([0-9]+)\]$/', $lastSegment, $matches)) {
$segments[] = Str::beforeLast($lastSegment, $matches[0]);

$key = $matches[1];
} else {
$key = "'".str_replace("'", "''", $lastSegment)."'";
}

[$field, $path] = $this->wrapJsonFieldAndPath(implode('->', $segments));

return $key.' in (select [key] from openjson('.$field.$path.'))';
}









protected function compileJsonLength($column, $operator, $value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($column);

return '(select count(*) from openjson('.$field.$path.')) '.$operator.' '.$value;
}







public function compileJsonValueCast($value)
{
return 'json_query('.$value.')';
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

return '('.$column.' '.$having['operator'].' '.$parameter.') != 0';
}









protected function compileDeleteWithoutJoins(Builder $query, $table, $where)
{
$sql = parent::compileDeleteWithoutJoins($query, $table, $where);

return ! is_null($query->limit) && $query->limit > 0 && $query->offset <= 0
? Str::replaceFirst('delete', 'delete top ('.$query->limit.')', $sql)
: $sql;
}







public function compileRandom($seed)
{
return 'NEWID()';
}








protected function compileLimit(Builder $query, $limit)
{
$limit = (int) $limit;

if ($limit && $query->offset > 0) {
return "fetch next {$limit} rows only";
}

return '';
}








protected function compileRowNumber($partition, $orders)
{
if (empty($orders)) {
$orders = 'order by (select 0)';
}

return parent::compileRowNumber($partition, $orders);
}








protected function compileOffset(Builder $query, $offset)
{
$offset = (int) $offset;

if ($offset) {
return "offset {$offset} rows";
}

return '';
}








protected function compileLock(Builder $query, $value)
{
return '';
}







protected function wrapUnion($sql)
{
return 'select * from ('.$sql.') as '.$this->wrapTable('temp_table');
}







public function compileExists(Builder $query)
{
$existsQuery = clone $query;

$existsQuery->columns = [];

return $this->compileSelect($existsQuery->selectRaw('1 [exists]')->limit(1));
}










protected function compileUpdateWithJoins(Builder $query, $table, $columns, $where)
{
$alias = last(explode(' as ', $table));

$joins = $this->compileJoins($query, $query->joins);

return "update {$alias} set {$columns} from {$table} {$joins} {$where}";
}










public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update)
{
$columns = $this->columnize(array_keys(reset($values)));

$sql = 'merge '.$this->wrapTable($query->from).' ';

$parameters = collect($values)->map(function ($record) {
return '('.$this->parameterize($record).')';
})->implode(', ');

$sql .= 'using (values '.$parameters.') '.$this->wrapTable('laravel_source').' ('.$columns.') ';

$on = collect($uniqueBy)->map(function ($column) use ($query) {
return $this->wrap('laravel_source.'.$column).' = '.$this->wrap($query->from.'.'.$column);
})->implode(' and ');

$sql .= 'on '.$on.' ';

if ($update) {
$update = collect($update)->map(function ($value, $key) {
return is_numeric($key)
? $this->wrap($value).' = '.$this->wrap('laravel_source.'.$value)
: $this->wrap($key).' = '.$this->parameter($value);
})->implode(', ');

$sql .= 'when matched then update set '.$update.' ';
}

$sql .= 'when not matched then insert ('.$columns.') values ('.$columns.');';

return $sql;
}








public function prepareBindingsForUpdate(array $bindings, array $values)
{
$cleanBindings = Arr::except($bindings, 'select');

return array_values(
array_merge($values, Arr::flatten($cleanBindings))
);
}








public function compileJoinLateral(JoinLateralClause $join, string $expression): string
{
$type = $join->type == 'left' ? 'outer' : 'cross';

return trim("{$type} apply {$expression}");
}







public function compileSavepoint($name)
{
return 'SAVE TRANSACTION '.$name;
}







public function compileSavepointRollBack($name)
{
return 'ROLLBACK TRANSACTION '.$name;
}






public function compileThreadCount()
{
return 'select count(*) Value from sys.dm_exec_sessions where status = N\'running\'';
}






public function getDateFormat()
{
return 'Y-m-d H:i:s.v';
}







protected function wrapValue($value)
{
return $value === '*' ? $value : '['.str_replace(']', ']]', $value).']';
}







protected function wrapJsonSelector($value)
{
[$field, $path] = $this->wrapJsonFieldAndPath($value);

return 'json_value('.$field.$path.')';
}







protected function wrapJsonBooleanValue($value)
{
return "'".$value."'";
}







public function wrapTable($table)
{
if (! $this->isExpression($table)) {
return $this->wrapTableValuedFunction(parent::wrapTable($table));
}

return $this->getValue($table);
}







protected function wrapTableValuedFunction($table)
{
if (preg_match('/^(.+?)(\(.*?\))]$/', $table, $matches) === 1) {
$table = $matches[1].']'.$matches[2];
}

return $table;
}
}
