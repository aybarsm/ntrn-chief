<?php

namespace Illuminate\Database\Query\Grammars;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Concerns\CompilesJsonPaths;
use Illuminate\Database\Grammar as BaseGrammar;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\Query\JoinLateralClause;
use Illuminate\Support\Arr;
use RuntimeException;

class Grammar extends BaseGrammar
{
use CompilesJsonPaths;






protected $operators = [];






protected $bitwiseOperators = [];






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
'limit',
'offset',
'lock',
];







public function compileSelect(Builder $query)
{
if (($query->unions || $query->havings) && $query->aggregate) {
return $this->compileUnionAggregate($query);
}




if (isset($query->groupLimit)) {
if (is_null($query->columns)) {
$query->columns = ['*'];
}

return $this->compileGroupLimit($query);
}




$original = $query->columns;

if (is_null($query->columns)) {
$query->columns = ['*'];
}




$sql = trim($this->concatenate(
$this->compileComponents($query))
);

if ($query->unions) {
$sql = $this->wrapUnion($sql).' '.$this->compileUnions($query);
}

$query->columns = $original;

return $sql;
}







protected function compileComponents(Builder $query)
{
$sql = [];

foreach ($this->selectComponents as $component) {
if (isset($query->$component)) {
$method = 'compile'.ucfirst($component);

$sql[$component] = $this->$method($query, $query->$component);
}
}

return $sql;
}








protected function compileAggregate(Builder $query, $aggregate)
{
$column = $this->columnize($aggregate['columns']);




if (is_array($query->distinct)) {
$column = 'distinct '.$this->columnize($query->distinct);
} elseif ($query->distinct && $column !== '*') {
$column = 'distinct '.$column;
}

return 'select '.$aggregate['function'].'('.$column.') as aggregate';
}








protected function compileColumns(Builder $query, $columns)
{



if (! is_null($query->aggregate)) {
return;
}

if ($query->distinct) {
$select = 'select distinct ';
} else {
$select = 'select ';
}

return $select.$this->columnize($columns);
}








protected function compileFrom(Builder $query, $table)
{
return 'from '.$this->wrapTable($table);
}








protected function compileJoins(Builder $query, $joins)
{
return collect($joins)->map(function ($join) use ($query) {
$table = $this->wrapTable($join->table);

$nestedJoins = is_null($join->joins) ? '' : ' '.$this->compileJoins($query, $join->joins);

$tableAndNestedJoins = is_null($join->joins) ? $table : '('.$table.$nestedJoins.')';

if ($join instanceof JoinLateralClause) {
return $this->compileJoinLateral($join, $tableAndNestedJoins);
}

return trim("{$join->type} join {$tableAndNestedJoins} {$this->compileWheres($join)}");
})->implode(' ');
}










public function compileJoinLateral(JoinLateralClause $join, string $expression): string
{
throw new RuntimeException('This database engine does not support lateral joins.');
}







public function compileWheres(Builder $query)
{



if (is_null($query->wheres)) {
return '';
}




if (count($sql = $this->compileWheresToArray($query)) > 0) {
return $this->concatenateWhereClauses($query, $sql);
}

return '';
}







protected function compileWheresToArray($query)
{
return collect($query->wheres)->map(function ($where) use ($query) {
return $where['boolean'].' '.$this->{"where{$where['type']}"}($query, $where);
})->all();
}








protected function concatenateWhereClauses($query, $sql)
{
$conjunction = $query instanceof JoinClause ? 'on' : 'where';

return $conjunction.' '.$this->removeLeadingBoolean(implode(' ', $sql));
}








protected function whereRaw(Builder $query, $where)
{
return $where['sql'] instanceof Expression ? $where['sql']->getValue($this) : $where['sql'];
}








protected function whereBasic(Builder $query, $where)
{
$value = $this->parameter($where['value']);

$operator = str_replace('?', '??', $where['operator']);

return $this->wrap($where['column']).' '.$operator.' '.$value;
}








protected function whereBitwise(Builder $query, $where)
{
return $this->whereBasic($query, $where);
}








protected function whereLike(Builder $query, $where)
{
if ($where['caseSensitive']) {
throw new RuntimeException('This database engine does not support case sensitive like operations.');
}

$where['operator'] = $where['not'] ? 'not like' : 'like';

return $this->whereBasic($query, $where);
}








protected function whereIn(Builder $query, $where)
{
if (! empty($where['values'])) {
return $this->wrap($where['column']).' in ('.$this->parameterize($where['values']).')';
}

return '0 = 1';
}








protected function whereNotIn(Builder $query, $where)
{
if (! empty($where['values'])) {
return $this->wrap($where['column']).' not in ('.$this->parameterize($where['values']).')';
}

return '1 = 1';
}










protected function whereNotInRaw(Builder $query, $where)
{
if (! empty($where['values'])) {
return $this->wrap($where['column']).' not in ('.implode(', ', $where['values']).')';
}

return '1 = 1';
}










protected function whereInRaw(Builder $query, $where)
{
if (! empty($where['values'])) {
return $this->wrap($where['column']).' in ('.implode(', ', $where['values']).')';
}

return '0 = 1';
}








protected function whereNull(Builder $query, $where)
{
return $this->wrap($where['column']).' is null';
}








protected function whereNotNull(Builder $query, $where)
{
return $this->wrap($where['column']).' is not null';
}








protected function whereBetween(Builder $query, $where)
{
$between = $where['not'] ? 'not between' : 'between';

$min = $this->parameter(is_array($where['values']) ? reset($where['values']) : $where['values'][0]);

$max = $this->parameter(is_array($where['values']) ? end($where['values']) : $where['values'][1]);

return $this->wrap($where['column']).' '.$between.' '.$min.' and '.$max;
}








protected function whereBetweenColumns(Builder $query, $where)
{
$between = $where['not'] ? 'not between' : 'between';

$min = $this->wrap(is_array($where['values']) ? reset($where['values']) : $where['values'][0]);

$max = $this->wrap(is_array($where['values']) ? end($where['values']) : $where['values'][1]);

return $this->wrap($where['column']).' '.$between.' '.$min.' and '.$max;
}








protected function whereDate(Builder $query, $where)
{
return $this->dateBasedWhere('date', $query, $where);
}








protected function whereTime(Builder $query, $where)
{
return $this->dateBasedWhere('time', $query, $where);
}








protected function whereDay(Builder $query, $where)
{
return $this->dateBasedWhere('day', $query, $where);
}








protected function whereMonth(Builder $query, $where)
{
return $this->dateBasedWhere('month', $query, $where);
}








protected function whereYear(Builder $query, $where)
{
return $this->dateBasedWhere('year', $query, $where);
}









protected function dateBasedWhere($type, Builder $query, $where)
{
$value = $this->parameter($where['value']);

return $type.'('.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
}








protected function whereColumn(Builder $query, $where)
{
return $this->wrap($where['first']).' '.$where['operator'].' '.$this->wrap($where['second']);
}








protected function whereNested(Builder $query, $where)
{



$offset = $where['query'] instanceof JoinClause ? 3 : 6;

return '('.substr($this->compileWheres($where['query']), $offset).')';
}








protected function whereSub(Builder $query, $where)
{
$select = $this->compileSelect($where['query']);

return $this->wrap($where['column']).' '.$where['operator']." ($select)";
}








protected function whereExists(Builder $query, $where)
{
return 'exists ('.$this->compileSelect($where['query']).')';
}








protected function whereNotExists(Builder $query, $where)
{
return 'not exists ('.$this->compileSelect($where['query']).')';
}








protected function whereRowValues(Builder $query, $where)
{
$columns = $this->columnize($where['columns']);

$values = $this->parameterize($where['values']);

return '('.$columns.') '.$where['operator'].' ('.$values.')';
}








protected function whereJsonBoolean(Builder $query, $where)
{
$column = $this->wrapJsonBooleanSelector($where['column']);

$value = $this->wrapJsonBooleanValue(
$this->parameter($where['value'])
);

return $column.' '.$where['operator'].' '.$value;
}








protected function whereJsonContains(Builder $query, $where)
{
$not = $where['not'] ? 'not ' : '';

return $not.$this->compileJsonContains(
$where['column'],
$this->parameter($where['value'])
);
}










protected function compileJsonContains($column, $value)
{
throw new RuntimeException('This database engine does not support JSON contains operations.');
}








protected function whereJsonOverlaps(Builder $query, $where)
{
$not = $where['not'] ? 'not ' : '';

return $not.$this->compileJsonOverlaps(
$where['column'],
$this->parameter($where['value'])
);
}










protected function compileJsonOverlaps($column, $value)
{
throw new RuntimeException('This database engine does not support JSON overlaps operations.');
}







public function prepareBindingForJsonContains($binding)
{
return json_encode($binding, JSON_UNESCAPED_UNICODE);
}








protected function whereJsonContainsKey(Builder $query, $where)
{
$not = $where['not'] ? 'not ' : '';

return $not.$this->compileJsonContainsKey(
$where['column']
);
}









protected function compileJsonContainsKey($column)
{
throw new RuntimeException('This database engine does not support JSON contains key operations.');
}








protected function whereJsonLength(Builder $query, $where)
{
return $this->compileJsonLength(
$where['column'],
$where['operator'],
$this->parameter($where['value'])
);
}











protected function compileJsonLength($column, $operator, $value)
{
throw new RuntimeException('This database engine does not support JSON length operations.');
}







public function compileJsonValueCast($value)
{
return $value;
}








public function whereFullText(Builder $query, $where)
{
throw new RuntimeException('This database engine does not support fulltext search operations.');
}








public function whereExpression(Builder $query, $where)
{
return $where['column']->getValue($this);
}








protected function compileGroups(Builder $query, $groups)
{
return 'group by '.$this->columnize($groups);
}







protected function compileHavings(Builder $query)
{
return 'having '.$this->removeLeadingBoolean(collect($query->havings)->map(function ($having) {
return $having['boolean'].' '.$this->compileHaving($having);
})->implode(' '));
}







protected function compileHaving(array $having)
{



return match ($having['type']) {
'Raw' => $having['sql'],
'between' => $this->compileHavingBetween($having),
'Null' => $this->compileHavingNull($having),
'NotNull' => $this->compileHavingNotNull($having),
'bit' => $this->compileHavingBit($having),
'Expression' => $this->compileHavingExpression($having),
'Nested' => $this->compileNestedHavings($having),
default => $this->compileBasicHaving($having),
};
}







protected function compileBasicHaving($having)
{
$column = $this->wrap($having['column']);

$parameter = $this->parameter($having['value']);

return $column.' '.$having['operator'].' '.$parameter;
}







protected function compileHavingBetween($having)
{
$between = $having['not'] ? 'not between' : 'between';

$column = $this->wrap($having['column']);

$min = $this->parameter(head($having['values']));

$max = $this->parameter(last($having['values']));

return $column.' '.$between.' '.$min.' and '.$max;
}







protected function compileHavingNull($having)
{
$column = $this->wrap($having['column']);

return $column.' is null';
}







protected function compileHavingNotNull($having)
{
$column = $this->wrap($having['column']);

return $column.' is not null';
}







protected function compileHavingBit($having)
{
$column = $this->wrap($having['column']);

$parameter = $this->parameter($having['value']);

return '('.$column.' '.$having['operator'].' '.$parameter.') != 0';
}







protected function compileHavingExpression($having)
{
return $having['column']->getValue($this);
}







protected function compileNestedHavings($having)
{
return '('.substr($this->compileHavings($having['query']), 7).')';
}








protected function compileOrders(Builder $query, $orders)
{
if (! empty($orders)) {
return 'order by '.implode(', ', $this->compileOrdersToArray($query, $orders));
}

return '';
}








protected function compileOrdersToArray(Builder $query, $orders)
{
return array_map(function ($order) {
return $order['sql'] ?? $this->wrap($order['column']).' '.$order['direction'];
}, $orders);
}







public function compileRandom($seed)
{
return 'RANDOM()';
}








protected function compileLimit(Builder $query, $limit)
{
return 'limit '.(int) $limit;
}







protected function compileGroupLimit(Builder $query)
{
$selectBindings = array_merge($query->getRawBindings()['select'], $query->getRawBindings()['order']);

$query->setBindings($selectBindings, 'select');
$query->setBindings([], 'order');

$limit = (int) $query->groupLimit['value'];
$offset = $query->offset;

if (isset($offset)) {
$offset = (int) $offset;
$limit += $offset;

$query->offset = null;
}

$components = $this->compileComponents($query);

$components['columns'] .= $this->compileRowNumber(
$query->groupLimit['column'],
$components['orders'] ?? ''
);

unset($components['orders']);

$table = $this->wrap('laravel_table');
$row = $this->wrap('laravel_row');

$sql = $this->concatenate($components);

$sql = 'select * from ('.$sql.') as '.$table.' where '.$row.' <= '.$limit;

if (isset($offset)) {
$sql .= ' and '.$row.' > '.$offset;
}

return $sql.' order by '.$row;
}








protected function compileRowNumber($partition, $orders)
{
$over = trim('partition by '.$this->wrap($partition).' '.$orders);

return ', row_number() over ('.$over.') as '.$this->wrap('laravel_row');
}








protected function compileOffset(Builder $query, $offset)
{
return 'offset '.(int) $offset;
}







protected function compileUnions(Builder $query)
{
$sql = '';

foreach ($query->unions as $union) {
$sql .= $this->compileUnion($union);
}

if (! empty($query->unionOrders)) {
$sql .= ' '.$this->compileOrders($query, $query->unionOrders);
}

if (isset($query->unionLimit)) {
$sql .= ' '.$this->compileLimit($query, $query->unionLimit);
}

if (isset($query->unionOffset)) {
$sql .= ' '.$this->compileOffset($query, $query->unionOffset);
}

return ltrim($sql);
}







protected function compileUnion(array $union)
{
$conjunction = $union['all'] ? ' union all ' : ' union ';

return $conjunction.$this->wrapUnion($union['query']->toSql());
}







protected function wrapUnion($sql)
{
return '('.$sql.')';
}







protected function compileUnionAggregate(Builder $query)
{
$sql = $this->compileAggregate($query, $query->aggregate);

$query->aggregate = null;

return $sql.' from ('.$this->compileSelect($query).') as '.$this->wrapTable('temp_table');
}







public function compileExists(Builder $query)
{
$select = $this->compileSelect($query);

return "select exists({$select}) as {$this->wrap('exists')}";
}








public function compileInsert(Builder $query, array $values)
{



$table = $this->wrapTable($query->from);

if (empty($values)) {
return "insert into {$table} default values";
}

if (! is_array(reset($values))) {
$values = [$values];
}

$columns = $this->columnize(array_keys(reset($values)));




$parameters = collect($values)->map(function ($record) {
return '('.$this->parameterize($record).')';
})->implode(', ');

return "insert into $table ($columns) values $parameters";
}










public function compileInsertOrIgnore(Builder $query, array $values)
{
throw new RuntimeException('This database engine does not support inserting while ignoring errors.');
}









public function compileInsertGetId(Builder $query, $values, $sequence)
{
return $this->compileInsert($query, $values);
}









public function compileInsertUsing(Builder $query, array $columns, string $sql)
{
$table = $this->wrapTable($query->from);

if (empty($columns) || $columns === ['*']) {
return "insert into {$table} $sql";
}

return "insert into {$table} ({$this->columnize($columns)}) $sql";
}











public function compileInsertOrIgnoreUsing(Builder $query, array $columns, string $sql)
{
throw new RuntimeException('This database engine does not support inserting while ignoring errors.');
}








public function compileUpdate(Builder $query, array $values)
{
$table = $this->wrapTable($query->from);

$columns = $this->compileUpdateColumns($query, $values);

$where = $this->compileWheres($query);

return trim(
isset($query->joins)
? $this->compileUpdateWithJoins($query, $table, $columns, $where)
: $this->compileUpdateWithoutJoins($query, $table, $columns, $where)
);
}








protected function compileUpdateColumns(Builder $query, array $values)
{
return collect($values)->map(function ($value, $key) {
return $this->wrap($key).' = '.$this->parameter($value);
})->implode(', ');
}










protected function compileUpdateWithoutJoins(Builder $query, $table, $columns, $where)
{
return "update {$table} set {$columns} {$where}";
}










protected function compileUpdateWithJoins(Builder $query, $table, $columns, $where)
{
$joins = $this->compileJoins($query, $query->joins);

return "update {$table} {$joins} set {$columns} {$where}";
}












public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update)
{
throw new RuntimeException('This database engine does not support upserts.');
}








public function prepareBindingsForUpdate(array $bindings, array $values)
{
$cleanBindings = Arr::except($bindings, ['select', 'join']);

$values = Arr::flatten(array_map(fn ($value) => value($value), $values));

return array_values(
array_merge($bindings['join'], $values, Arr::flatten($cleanBindings))
);
}







public function compileDelete(Builder $query)
{
$table = $this->wrapTable($query->from);

$where = $this->compileWheres($query);

return trim(
isset($query->joins)
? $this->compileDeleteWithJoins($query, $table, $where)
: $this->compileDeleteWithoutJoins($query, $table, $where)
);
}









protected function compileDeleteWithoutJoins(Builder $query, $table, $where)
{
return "delete from {$table} {$where}";
}









protected function compileDeleteWithJoins(Builder $query, $table, $where)
{
$alias = last(explode(' as ', $table));

$joins = $this->compileJoins($query, $query->joins);

return "delete {$alias} from {$table} {$joins} {$where}";
}







public function prepareBindingsForDelete(array $bindings)
{
return Arr::flatten(
Arr::except($bindings, 'select')
);
}







public function compileTruncate(Builder $query)
{
return ['truncate table '.$this->wrapTable($query->from) => []];
}








protected function compileLock(Builder $query, $value)
{
return is_string($value) ? $value : '';
}






public function compileThreadCount()
{
return null;
}






public function supportsSavepoints()
{
return true;
}







public function compileSavepoint($name)
{
return 'SAVEPOINT '.$name;
}







public function compileSavepointRollBack($name)
{
return 'ROLLBACK TO SAVEPOINT '.$name;
}







protected function wrapJsonBooleanSelector($value)
{
return $this->wrapJsonSelector($value);
}







protected function wrapJsonBooleanValue($value)
{
return $value;
}







protected function concatenate($segments)
{
return implode(' ', array_filter($segments, function ($value) {
return (string) $value !== '';
}));
}







protected function removeLeadingBoolean($value)
{
return preg_replace('/and |or /i', '', $value, 1);
}








public function substituteBindingsIntoRawSql($sql, $bindings)
{
$bindings = array_map(fn ($value) => $this->escape($value, is_resource($value) || gettype($value) === 'resource (closed)'), $bindings);

$query = '';

$isStringLiteral = false;

for ($i = 0; $i < strlen($sql); $i++) {
$char = $sql[$i];
$nextChar = $sql[$i + 1] ?? null;




if (in_array($char.$nextChar, ["\'", "''", '??'])) {
$query .= $char.$nextChar;
$i += 1;
} elseif ($char === "'") { 
$query .= $char;
$isStringLiteral = ! $isStringLiteral;
} elseif ($char === '?' && ! $isStringLiteral) { 
$query .= array_shift($bindings) ?? '?';
} else { 
$query .= $char;
}
}

return $query;
}






public function getOperators()
{
return $this->operators;
}






public function getBitwiseOperators()
{
return $this->bitwiseOperators;
}
}
