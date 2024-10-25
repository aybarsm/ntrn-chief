<?php

namespace Illuminate\Database\Query;

use BackedEnum;
use Carbon\CarbonPeriod;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Contracts\Database\Query\ConditionExpression;
use Illuminate\Contracts\Database\Query\Expression as ExpressionContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Concerns\ExplainsQueries;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use UnitEnum;

use function Illuminate\Support\enum_value;

class Builder implements BuilderContract
{
/**
@use */
use BuildsQueries, ExplainsQueries, ForwardsCalls, Macroable {
__call as macroCall;
}






public $connection;






public $grammar;






public $processor;






public $bindings = [
'select' => [],
'from' => [],
'join' => [],
'where' => [],
'groupBy' => [],
'having' => [],
'order' => [],
'union' => [],
'unionOrder' => [],
];






public $aggregate;






public $columns;








public $distinct = false;






public $from;






public $indexHint;






public $joins;






public $wheres = [];






public $groups;






public $havings;






public $orders;






public $limit;






public $groupLimit;






public $offset;






public $unions;






public $unionLimit;






public $unionOffset;






public $unionOrders;






public $lock;






public $beforeQueryCallbacks = [];






protected $afterQueryCallbacks = [];






public $operators = [
'=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
'like', 'like binary', 'not like', 'ilike',
'&', '|', '^', '<<', '>>', '&~', 'is', 'is not',
'rlike', 'not rlike', 'regexp', 'not regexp',
'~', '~*', '!~', '!~*', 'similar to',
'not similar to', 'not ilike', '~~*', '!~~*',
];






public $bitwiseOperators = [
'&', '|', '^', '<<', '>>', '&~',
];






public $useWritePdo = false;









public function __construct(ConnectionInterface $connection,
?Grammar $grammar = null,
?Processor $processor = null)
{
$this->connection = $connection;
$this->grammar = $grammar ?: $connection->getQueryGrammar();
$this->processor = $processor ?: $connection->getPostProcessor();
}







public function select($columns = ['*'])
{
$this->columns = [];
$this->bindings['select'] = [];

$columns = is_array($columns) ? $columns : func_get_args();

foreach ($columns as $as => $column) {
if (is_string($as) && $this->isQueryable($column)) {
$this->selectSub($column, $as);
} else {
$this->columns[] = $column;
}
}

return $this;
}










public function selectSub($query, $as)
{
[$query, $bindings] = $this->createSub($query);

return $this->selectRaw(
'('.$query.') as '.$this->grammar->wrap($as), $bindings
);
}








public function selectRaw($expression, array $bindings = [])
{
$this->addSelect(new Expression($expression));

if ($bindings) {
$this->addBinding($bindings, 'select');
}

return $this;
}










public function fromSub($query, $as)
{
[$query, $bindings] = $this->createSub($query);

return $this->fromRaw('('.$query.') as '.$this->grammar->wrapTable($as), $bindings);
}








public function fromRaw($expression, $bindings = [])
{
$this->from = new Expression($expression);

$this->addBinding($bindings, 'from');

return $this;
}







protected function createSub($query)
{



if ($query instanceof Closure) {
$callback = $query;

$callback($query = $this->forSubQuery());
}

return $this->parseSub($query);
}









protected function parseSub($query)
{
if ($query instanceof self || $query instanceof EloquentBuilder || $query instanceof Relation) {
$query = $this->prependDatabaseNameIfCrossDatabaseQuery($query);

return [$query->toSql(), $query->getBindings()];
} elseif (is_string($query)) {
return [$query, []];
} else {
throw new InvalidArgumentException(
'A subquery must be a query builder instance, a Closure, or a string.'
);
}
}







protected function prependDatabaseNameIfCrossDatabaseQuery($query)
{
if ($query->getConnection()->getDatabaseName() !==
$this->getConnection()->getDatabaseName()) {
$databaseName = $query->getConnection()->getDatabaseName();

if (! str_starts_with($query->from, $databaseName) && ! str_contains($query->from, '.')) {
$query->from($databaseName.'.'.$query->from);
}
}

return $query;
}







public function addSelect($column)
{
$columns = is_array($column) ? $column : func_get_args();

foreach ($columns as $as => $column) {
if (is_string($as) && $this->isQueryable($column)) {
if (is_null($this->columns)) {
$this->select($this->from.'.*');
}

$this->selectSub($column, $as);
} else {
if (is_array($this->columns) && in_array($column, $this->columns, true)) {
continue;
}

$this->columns[] = $column;
}
}

return $this;
}






public function distinct()
{
$columns = func_get_args();

if (count($columns) > 0) {
$this->distinct = is_array($columns[0]) || is_bool($columns[0]) ? $columns[0] : $columns;
} else {
$this->distinct = true;
}

return $this;
}








public function from($table, $as = null)
{
if ($this->isQueryable($table)) {
return $this->fromSub($table, $as);
}

$this->from = $as ? "{$table} as {$as}" : $table;

return $this;
}







public function useIndex($index)
{
$this->indexHint = new IndexHint('hint', $index);

return $this;
}







public function forceIndex($index)
{
$this->indexHint = new IndexHint('force', $index);

return $this;
}







public function ignoreIndex($index)
{
$this->indexHint = new IndexHint('ignore', $index);

return $this;
}












public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
{
$join = $this->newJoinClause($this, $type, $table);




if ($first instanceof Closure) {
$first($join);

$this->joins[] = $join;

$this->addBinding($join->getBindings(), 'join');
}




else {
$method = $where ? 'where' : 'on';

$this->joins[] = $join->$method($first, $operator, $second);

$this->addBinding($join->getBindings(), 'join');
}

return $this;
}











public function joinWhere($table, $first, $operator, $second, $type = 'inner')
{
return $this->join($table, $first, $operator, $second, $type, true);
}















public function joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
{
[$query, $bindings] = $this->createSub($query);

$expression = '('.$query.') as '.$this->grammar->wrapTable($as);

$this->addBinding($bindings, 'join');

return $this->join(new Expression($expression), $first, $operator, $second, $type, $where);
}









public function joinLateral($query, string $as, string $type = 'inner')
{
[$query, $bindings] = $this->createSub($query);

$expression = '('.$query.') as '.$this->grammar->wrapTable($as);

$this->addBinding($bindings, 'join');

$this->joins[] = $this->newJoinLateralClause($this, $type, new Expression($expression));

return $this;
}








public function leftJoinLateral($query, string $as)
{
return $this->joinLateral($query, $as, 'left');
}










public function leftJoin($table, $first, $operator = null, $second = null)
{
return $this->join($table, $first, $operator, $second, 'left');
}










public function leftJoinWhere($table, $first, $operator, $second)
{
return $this->joinWhere($table, $first, $operator, $second, 'left');
}











public function leftJoinSub($query, $as, $first, $operator = null, $second = null)
{
return $this->joinSub($query, $as, $first, $operator, $second, 'left');
}










public function rightJoin($table, $first, $operator = null, $second = null)
{
return $this->join($table, $first, $operator, $second, 'right');
}










public function rightJoinWhere($table, $first, $operator, $second)
{
return $this->joinWhere($table, $first, $operator, $second, 'right');
}











public function rightJoinSub($query, $as, $first, $operator = null, $second = null)
{
return $this->joinSub($query, $as, $first, $operator, $second, 'right');
}










public function crossJoin($table, $first = null, $operator = null, $second = null)
{
if ($first) {
return $this->join($table, $first, $operator, $second, 'cross');
}

$this->joins[] = $this->newJoinClause($this, 'cross', $table);

return $this;
}








public function crossJoinSub($query, $as)
{
[$query, $bindings] = $this->createSub($query);

$expression = '('.$query.') as '.$this->grammar->wrapTable($as);

$this->addBinding($bindings, 'join');

$this->joins[] = $this->newJoinClause($this, 'cross', new Expression($expression));

return $this;
}









protected function newJoinClause(self $parentQuery, $type, $table)
{
return new JoinClause($parentQuery, $type, $table);
}









protected function newJoinLateralClause(self $parentQuery, $type, $table)
{
return new JoinLateralClause($parentQuery, $type, $table);
}








public function mergeWheres($wheres, $bindings)
{
$this->wheres = array_merge($this->wheres, (array) $wheres);

$this->bindings['where'] = array_values(
array_merge($this->bindings['where'], (array) $bindings)
);

return $this;
}










public function where($column, $operator = null, $value = null, $boolean = 'and')
{
if ($column instanceof ConditionExpression) {
$type = 'Expression';

$this->wheres[] = compact('type', 'column', 'boolean');

return $this;
}




if (is_array($column)) {
return $this->addArrayOfWheres($column, $boolean);
}




[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);




if ($column instanceof Closure && is_null($operator)) {
return $this->whereNested($column, $boolean);
}




if ($this->isQueryable($column) && ! is_null($operator)) {
[$sub, $bindings] = $this->createSub($column);

return $this->addBinding($bindings, 'where')
->where(new Expression('('.$sub.')'), $operator, $value, $boolean);
}




if ($this->invalidOperator($operator)) {
[$value, $operator] = [$operator, '='];
}




if ($this->isQueryable($value)) {
return $this->whereSub($column, $operator, $value, $boolean);
}




if (is_null($value)) {
return $this->whereNull($column, $boolean, $operator !== '=');
}

$type = 'Basic';

$columnString = ($column instanceof ExpressionContract)
? $this->grammar->getValue($column)
: $column;




if (str_contains($columnString, '->') && is_bool($value)) {
$value = new Expression($value ? 'true' : 'false');

if (is_string($column)) {
$type = 'JsonBoolean';
}
}

if ($this->isBitwiseOperator($operator)) {
$type = 'Bitwise';
}




$this->wheres[] = compact(
'type', 'column', 'operator', 'value', 'boolean'
);

if (! $value instanceof ExpressionContract) {
$this->addBinding($this->flattenValue($value), 'where');
}

return $this;
}









protected function addArrayOfWheres($column, $boolean, $method = 'where')
{
return $this->whereNested(function ($query) use ($column, $method, $boolean) {
foreach ($column as $key => $value) {
if (is_numeric($key) && is_array($value)) {
$query->{$method}(...array_values($value), boolean: $boolean);
} else {
$query->{$method}($key, '=', $value, $boolean);
}
}
}, $boolean);
}











public function prepareValueAndOperator($value, $operator, $useDefault = false)
{
if ($useDefault) {
return [$operator, '='];
} elseif ($this->invalidOperatorAndValue($operator, $value)) {
throw new InvalidArgumentException('Illegal operator and value combination.');
}

return [$value, $operator];
}










protected function invalidOperatorAndValue($operator, $value)
{
return is_null($value) && in_array($operator, $this->operators) &&
! in_array($operator, ['=', '<>', '!=']);
}







protected function invalidOperator($operator)
{
return ! is_string($operator) || (! in_array(strtolower($operator), $this->operators, true) &&
! in_array(strtolower($operator), $this->grammar->getOperators(), true));
}







protected function isBitwiseOperator($operator)
{
return in_array(strtolower($operator), $this->bitwiseOperators, true) ||
in_array(strtolower($operator), $this->grammar->getBitwiseOperators(), true);
}









public function orWhere($column, $operator = null, $value = null)
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

return $this->where($column, $operator, $value, 'or');
}










public function whereNot($column, $operator = null, $value = null, $boolean = 'and')
{
if (is_array($column)) {
return $this->whereNested(function ($query) use ($column, $operator, $value, $boolean) {
$query->where($column, $operator, $value, $boolean);
}, $boolean.' not');
}

return $this->where($column, $operator, $value, $boolean.' not');
}









public function orWhereNot($column, $operator = null, $value = null)
{
return $this->whereNot($column, $operator, $value, 'or');
}










public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
{



if (is_array($first)) {
return $this->addArrayOfWheres($first, $boolean, 'whereColumn');
}




if ($this->invalidOperator($operator)) {
[$second, $operator] = [$operator, '='];
}




$type = 'Column';

$this->wheres[] = compact(
'type', 'first', 'operator', 'second', 'boolean'
);

return $this;
}









public function orWhereColumn($first, $operator = null, $second = null)
{
return $this->whereColumn($first, $operator, $second, 'or');
}









public function whereRaw($sql, $bindings = [], $boolean = 'and')
{
$this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];

$this->addBinding((array) $bindings, 'where');

return $this;
}








public function orWhereRaw($sql, $bindings = [])
{
return $this->whereRaw($sql, $bindings, 'or');
}











public function whereLike($column, $value, $caseSensitive = false, $boolean = 'and', $not = false)
{
$type = 'Like';

$this->wheres[] = compact('type', 'column', 'value', 'caseSensitive', 'boolean', 'not');

if (method_exists($this->grammar, 'prepareWhereLikeBinding')) {
$value = $this->grammar->prepareWhereLikeBinding($value, $caseSensitive);
}

$this->addBinding($value);

return $this;
}









public function orWhereLike($column, $value, $caseSensitive = false)
{
return $this->whereLike($column, $value, $caseSensitive, 'or', false);
}










public function whereNotLike($column, $value, $caseSensitive = false, $boolean = 'and')
{
return $this->whereLike($column, $value, $caseSensitive, $boolean, true);
}









public function orWhereNotLike($column, $value, $caseSensitive = false)
{
return $this->whereNotLike($column, $value, $caseSensitive, 'or');
}










public function whereIn($column, $values, $boolean = 'and', $not = false)
{
$type = $not ? 'NotIn' : 'In';




if ($this->isQueryable($values)) {
[$query, $bindings] = $this->createSub($values);

$values = [new Expression($query)];

$this->addBinding($bindings, 'where');
}




if ($values instanceof Arrayable) {
$values = $values->toArray();
}

$this->wheres[] = compact('type', 'column', 'values', 'boolean');

if (count($values) !== count(Arr::flatten($values, 1))) {
throw new InvalidArgumentException('Nested arrays may not be passed to whereIn method.');
}




$this->addBinding($this->cleanBindings($values), 'where');

return $this;
}








public function orWhereIn($column, $values)
{
return $this->whereIn($column, $values, 'or');
}









public function whereNotIn($column, $values, $boolean = 'and')
{
return $this->whereIn($column, $values, $boolean, true);
}








public function orWhereNotIn($column, $values)
{
return $this->whereNotIn($column, $values, 'or');
}










public function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
{
$type = $not ? 'NotInRaw' : 'InRaw';

if ($values instanceof Arrayable) {
$values = $values->toArray();
}

$values = Arr::flatten($values);

foreach ($values as &$value) {
$value = (int) ($value instanceof BackedEnum ? $value->value : $value);
}

$this->wheres[] = compact('type', 'column', 'values', 'boolean');

return $this;
}








public function orWhereIntegerInRaw($column, $values)
{
return $this->whereIntegerInRaw($column, $values, 'or');
}









public function whereIntegerNotInRaw($column, $values, $boolean = 'and')
{
return $this->whereIntegerInRaw($column, $values, $boolean, true);
}








public function orWhereIntegerNotInRaw($column, $values)
{
return $this->whereIntegerNotInRaw($column, $values, 'or');
}









public function whereNull($columns, $boolean = 'and', $not = false)
{
$type = $not ? 'NotNull' : 'Null';

foreach (Arr::wrap($columns) as $column) {
$this->wheres[] = compact('type', 'column', 'boolean');
}

return $this;
}







public function orWhereNull($column)
{
return $this->whereNull($column, 'or');
}








public function whereNotNull($columns, $boolean = 'and')
{
return $this->whereNull($columns, $boolean, true);
}










public function whereBetween($column, iterable $values, $boolean = 'and', $not = false)
{
$type = 'between';

if ($values instanceof CarbonPeriod) {
$values = [$values->getStartDate(), $values->getEndDate()];
}

$this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');

$this->addBinding(array_slice($this->cleanBindings(Arr::flatten($values)), 0, 2), 'where');

return $this;
}










public function whereBetweenColumns($column, array $values, $boolean = 'and', $not = false)
{
$type = 'betweenColumns';

$this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');

return $this;
}








public function orWhereBetween($column, iterable $values)
{
return $this->whereBetween($column, $values, 'or');
}








public function orWhereBetweenColumns($column, array $values)
{
return $this->whereBetweenColumns($column, $values, 'or');
}









public function whereNotBetween($column, iterable $values, $boolean = 'and')
{
return $this->whereBetween($column, $values, $boolean, true);
}









public function whereNotBetweenColumns($column, array $values, $boolean = 'and')
{
return $this->whereBetweenColumns($column, $values, $boolean, true);
}








public function orWhereNotBetween($column, iterable $values)
{
return $this->whereNotBetween($column, $values, 'or');
}








public function orWhereNotBetweenColumns($column, array $values)
{
return $this->whereNotBetweenColumns($column, $values, 'or');
}







public function orWhereNotNull($column)
{
return $this->whereNotNull($column, 'or');
}










public function whereDate($column, $operator, $value = null, $boolean = 'and')
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);




if ($this->invalidOperator($operator)) {
[$value, $operator] = [$operator, '='];
}

$value = $this->flattenValue($value);

if ($value instanceof DateTimeInterface) {
$value = $value->format('Y-m-d');
}

return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
}









public function orWhereDate($column, $operator, $value = null)
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

return $this->whereDate($column, $operator, $value, 'or');
}










public function whereTime($column, $operator, $value = null, $boolean = 'and')
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);




if ($this->invalidOperator($operator)) {
[$value, $operator] = [$operator, '='];
}

$value = $this->flattenValue($value);

if ($value instanceof DateTimeInterface) {
$value = $value->format('H:i:s');
}

return $this->addDateBasedWhere('Time', $column, $operator, $value, $boolean);
}









public function orWhereTime($column, $operator, $value = null)
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

return $this->whereTime($column, $operator, $value, 'or');
}










public function whereDay($column, $operator, $value = null, $boolean = 'and')
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);




if ($this->invalidOperator($operator)) {
[$value, $operator] = [$operator, '='];
}

$value = $this->flattenValue($value);

if ($value instanceof DateTimeInterface) {
$value = $value->format('d');
}

if (! $value instanceof ExpressionContract) {
$value = sprintf('%02d', $value);
}

return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
}









public function orWhereDay($column, $operator, $value = null)
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

return $this->whereDay($column, $operator, $value, 'or');
}










public function whereMonth($column, $operator, $value = null, $boolean = 'and')
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);




if ($this->invalidOperator($operator)) {
[$value, $operator] = [$operator, '='];
}

$value = $this->flattenValue($value);

if ($value instanceof DateTimeInterface) {
$value = $value->format('m');
}

if (! $value instanceof ExpressionContract) {
$value = sprintf('%02d', $value);
}

return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
}









public function orWhereMonth($column, $operator, $value = null)
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

return $this->whereMonth($column, $operator, $value, 'or');
}










public function whereYear($column, $operator, $value = null, $boolean = 'and')
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);




if ($this->invalidOperator($operator)) {
[$value, $operator] = [$operator, '='];
}

$value = $this->flattenValue($value);

if ($value instanceof DateTimeInterface) {
$value = $value->format('Y');
}

return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
}









public function orWhereYear($column, $operator, $value = null)
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

return $this->whereYear($column, $operator, $value, 'or');
}











protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
{
$this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');

if (! $value instanceof ExpressionContract) {
$this->addBinding($value, 'where');
}

return $this;
}








public function whereNested(Closure $callback, $boolean = 'and')
{
$callback($query = $this->forNestedWhere());

return $this->addNestedWhereQuery($query, $boolean);
}






public function forNestedWhere()
{
return $this->newQuery()->from($this->from);
}








public function addNestedWhereQuery($query, $boolean = 'and')
{
if (count($query->wheres)) {
$type = 'Nested';

$this->wheres[] = compact('type', 'query', 'boolean');

$this->addBinding($query->getRawBindings()['where'], 'where');
}

return $this;
}










protected function whereSub($column, $operator, $callback, $boolean)
{
$type = 'Sub';

if ($callback instanceof Closure) {



$callback($query = $this->forSubQuery());
} else {
$query = $callback instanceof EloquentBuilder ? $callback->toBase() : $callback;
}

$this->wheres[] = compact(
'type', 'column', 'operator', 'query', 'boolean'
);

$this->addBinding($query->getBindings(), 'where');

return $this;
}









public function whereExists($callback, $boolean = 'and', $not = false)
{
if ($callback instanceof Closure) {
$query = $this->forSubQuery();




$callback($query);
} else {
$query = $callback instanceof EloquentBuilder ? $callback->toBase() : $callback;
}

return $this->addWhereExistsQuery($query, $boolean, $not);
}








public function orWhereExists($callback, $not = false)
{
return $this->whereExists($callback, 'or', $not);
}








public function whereNotExists($callback, $boolean = 'and')
{
return $this->whereExists($callback, $boolean, true);
}







public function orWhereNotExists($callback)
{
return $this->orWhereExists($callback, true);
}









public function addWhereExistsQuery(self $query, $boolean = 'and', $not = false)
{
$type = $not ? 'NotExists' : 'Exists';

$this->wheres[] = compact('type', 'query', 'boolean');

$this->addBinding($query->getBindings(), 'where');

return $this;
}












public function whereRowValues($columns, $operator, $values, $boolean = 'and')
{
if (count($columns) !== count($values)) {
throw new InvalidArgumentException('The number of columns must match the number of values');
}

$type = 'RowValues';

$this->wheres[] = compact('type', 'columns', 'operator', 'values', 'boolean');

$this->addBinding($this->cleanBindings($values));

return $this;
}









public function orWhereRowValues($columns, $operator, $values)
{
return $this->whereRowValues($columns, $operator, $values, 'or');
}










public function whereJsonContains($column, $value, $boolean = 'and', $not = false)
{
$type = 'JsonContains';

$this->wheres[] = compact('type', 'column', 'value', 'boolean', 'not');

if (! $value instanceof ExpressionContract) {
$this->addBinding($this->grammar->prepareBindingForJsonContains($value));
}

return $this;
}








public function orWhereJsonContains($column, $value)
{
return $this->whereJsonContains($column, $value, 'or');
}









public function whereJsonDoesntContain($column, $value, $boolean = 'and')
{
return $this->whereJsonContains($column, $value, $boolean, true);
}








public function orWhereJsonDoesntContain($column, $value)
{
return $this->whereJsonDoesntContain($column, $value, 'or');
}










public function whereJsonOverlaps($column, $value, $boolean = 'and', $not = false)
{
$type = 'JsonOverlaps';

$this->wheres[] = compact('type', 'column', 'value', 'boolean', 'not');

if (! $value instanceof ExpressionContract) {
$this->addBinding($this->grammar->prepareBindingForJsonContains($value));
}

return $this;
}








public function orWhereJsonOverlaps($column, $value)
{
return $this->whereJsonOverlaps($column, $value, 'or');
}









public function whereJsonDoesntOverlap($column, $value, $boolean = 'and')
{
return $this->whereJsonOverlaps($column, $value, $boolean, true);
}








public function orWhereJsonDoesntOverlap($column, $value)
{
return $this->whereJsonDoesntOverlap($column, $value, 'or');
}









public function whereJsonContainsKey($column, $boolean = 'and', $not = false)
{
$type = 'JsonContainsKey';

$this->wheres[] = compact('type', 'column', 'boolean', 'not');

return $this;
}







public function orWhereJsonContainsKey($column)
{
return $this->whereJsonContainsKey($column, 'or');
}








public function whereJsonDoesntContainKey($column, $boolean = 'and')
{
return $this->whereJsonContainsKey($column, $boolean, true);
}







public function orWhereJsonDoesntContainKey($column)
{
return $this->whereJsonDoesntContainKey($column, 'or');
}










public function whereJsonLength($column, $operator, $value = null, $boolean = 'and')
{
$type = 'JsonLength';

[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);




if ($this->invalidOperator($operator)) {
[$value, $operator] = [$operator, '='];
}

$this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

if (! $value instanceof ExpressionContract) {
$this->addBinding((int) $this->flattenValue($value));
}

return $this;
}









public function orWhereJsonLength($column, $operator, $value = null)
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

return $this->whereJsonLength($column, $operator, $value, 'or');
}








public function dynamicWhere($method, $parameters)
{
$finder = substr($method, 5);

$segments = preg_split(
'/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE
);




$connector = 'and';

$index = 0;

foreach ($segments as $segment) {



if ($segment !== 'And' && $segment !== 'Or') {
$this->addDynamic($segment, $connector, $parameters, $index);

$index++;
}




else {
$connector = $segment;
}
}

return $this;
}










protected function addDynamic($segment, $connector, $parameters, $index)
{



$bool = strtolower($connector);

$this->where(Str::snake($segment), '=', $parameters[$index], $bool);
}









public function whereFullText($columns, $value, array $options = [], $boolean = 'and')
{
$type = 'Fulltext';

$columns = (array) $columns;

$this->wheres[] = compact('type', 'columns', 'value', 'options', 'boolean');

$this->addBinding($value);

return $this;
}








public function orWhereFullText($columns, $value, array $options = [])
{
return $this->whereFulltext($columns, $value, $options, 'or');
}










public function whereAll($columns, $operator = null, $value = null, $boolean = 'and')
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

$this->whereNested(function ($query) use ($columns, $operator, $value) {
foreach ($columns as $column) {
$query->where($column, $operator, $value, 'and');
}
}, $boolean);

return $this;
}









public function orWhereAll($columns, $operator = null, $value = null)
{
return $this->whereAll($columns, $operator, $value, 'or');
}










public function whereAny($columns, $operator = null, $value = null, $boolean = 'and')
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

$this->whereNested(function ($query) use ($columns, $operator, $value) {
foreach ($columns as $column) {
$query->where($column, $operator, $value, 'or');
}
}, $boolean);

return $this;
}









public function orWhereAny($columns, $operator = null, $value = null)
{
return $this->whereAny($columns, $operator, $value, 'or');
}










public function whereNone($columns, $operator = null, $value = null, $boolean = 'and')
{
return $this->whereAny($columns, $operator, $value, $boolean.' not');
}









public function orWhereNone($columns, $operator = null, $value = null)
{
return $this->whereNone($columns, $operator, $value, 'or');
}







public function groupBy(...$groups)
{
foreach ($groups as $group) {
$this->groups = array_merge(
(array) $this->groups,
Arr::wrap($group)
);
}

return $this;
}








public function groupByRaw($sql, array $bindings = [])
{
$this->groups[] = new Expression($sql);

$this->addBinding($bindings, 'groupBy');

return $this;
}










public function having($column, $operator = null, $value = null, $boolean = 'and')
{
$type = 'Basic';

if ($column instanceof ConditionExpression) {
$type = 'Expression';

$this->havings[] = compact('type', 'column', 'boolean');

return $this;
}




[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

if ($column instanceof Closure && is_null($operator)) {
return $this->havingNested($column, $boolean);
}




if ($this->invalidOperator($operator)) {
[$value, $operator] = [$operator, '='];
}

if ($this->isBitwiseOperator($operator)) {
$type = 'Bitwise';
}

$this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');

if (! $value instanceof ExpressionContract) {
$this->addBinding($this->flattenValue($value), 'having');
}

return $this;
}









public function orHaving($column, $operator = null, $value = null)
{
[$value, $operator] = $this->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

return $this->having($column, $operator, $value, 'or');
}








public function havingNested(Closure $callback, $boolean = 'and')
{
$callback($query = $this->forNestedWhere());

return $this->addNestedHavingQuery($query, $boolean);
}








public function addNestedHavingQuery($query, $boolean = 'and')
{
if (count($query->havings)) {
$type = 'Nested';

$this->havings[] = compact('type', 'query', 'boolean');

$this->addBinding($query->getRawBindings()['having'], 'having');
}

return $this;
}









public function havingNull($columns, $boolean = 'and', $not = false)
{
$type = $not ? 'NotNull' : 'Null';

foreach (Arr::wrap($columns) as $column) {
$this->havings[] = compact('type', 'column', 'boolean');
}

return $this;
}







public function orHavingNull($column)
{
return $this->havingNull($column, 'or');
}








public function havingNotNull($columns, $boolean = 'and')
{
return $this->havingNull($columns, $boolean, true);
}







public function orHavingNotNull($column)
{
return $this->havingNotNull($column, 'or');
}










public function havingBetween($column, iterable $values, $boolean = 'and', $not = false)
{
$type = 'between';

if ($values instanceof CarbonPeriod) {
$values = [$values->getStartDate(), $values->getEndDate()];
}

$this->havings[] = compact('type', 'column', 'values', 'boolean', 'not');

$this->addBinding(array_slice($this->cleanBindings(Arr::flatten($values)), 0, 2), 'having');

return $this;
}









public function havingRaw($sql, array $bindings = [], $boolean = 'and')
{
$type = 'Raw';

$this->havings[] = compact('type', 'sql', 'boolean');

$this->addBinding($bindings, 'having');

return $this;
}








public function orHavingRaw($sql, array $bindings = [])
{
return $this->havingRaw($sql, $bindings, 'or');
}










public function orderBy($column, $direction = 'asc')
{
if ($this->isQueryable($column)) {
[$query, $bindings] = $this->createSub($column);

$column = new Expression('('.$query.')');

$this->addBinding($bindings, $this->unions ? 'unionOrder' : 'order');
}

$direction = strtolower($direction);

if (! in_array($direction, ['asc', 'desc'], true)) {
throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
}

$this->{$this->unions ? 'unionOrders' : 'orders'}[] = [
'column' => $column,
'direction' => $direction,
];

return $this;
}







public function orderByDesc($column)
{
return $this->orderBy($column, 'desc');
}







public function latest($column = 'created_at')
{
return $this->orderBy($column, 'desc');
}







public function oldest($column = 'created_at')
{
return $this->orderBy($column, 'asc');
}







public function inRandomOrder($seed = '')
{
return $this->orderByRaw($this->grammar->compileRandom($seed));
}








public function orderByRaw($sql, $bindings = [])
{
$type = 'Raw';

$this->{$this->unions ? 'unionOrders' : 'orders'}[] = compact('type', 'sql');

$this->addBinding($bindings, $this->unions ? 'unionOrder' : 'order');

return $this;
}







public function skip($value)
{
return $this->offset($value);
}







public function offset($value)
{
$property = $this->unions ? 'unionOffset' : 'offset';

$this->$property = max(0, (int) $value);

return $this;
}







public function take($value)
{
return $this->limit($value);
}







public function limit($value)
{
$property = $this->unions ? 'unionLimit' : 'limit';

if ($value >= 0) {
$this->$property = ! is_null($value) ? (int) $value : null;
}

return $this;
}








public function groupLimit($value, $column)
{
if ($value >= 0) {
$this->groupLimit = compact('value', 'column');
}

return $this;
}








public function forPage($page, $perPage = 15)
{
return $this->offset(($page - 1) * $perPage)->limit($perPage);
}









public function forPageBeforeId($perPage = 15, $lastId = 0, $column = 'id')
{
$this->orders = $this->removeExistingOrdersFor($column);

if (! is_null($lastId)) {
$this->where($column, '<', $lastId);
}

return $this->orderBy($column, 'desc')
->limit($perPage);
}









public function forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
{
$this->orders = $this->removeExistingOrdersFor($column);

if (! is_null($lastId)) {
$this->where($column, '>', $lastId);
}

return $this->orderBy($column, 'asc')
->limit($perPage);
}








public function reorder($column = null, $direction = 'asc')
{
$this->orders = null;
$this->unionOrders = null;
$this->bindings['order'] = [];
$this->bindings['unionOrder'] = [];

if ($column) {
return $this->orderBy($column, $direction);
}

return $this;
}







protected function removeExistingOrdersFor($column)
{
return Collection::make($this->orders)
->reject(function ($order) use ($column) {
return isset($order['column'])
? $order['column'] === $column : false;
})->values()->all();
}








public function union($query, $all = false)
{
if ($query instanceof Closure) {
$query($query = $this->newQuery());
}

$this->unions[] = compact('query', 'all');

$this->addBinding($query->getBindings(), 'union');

return $this;
}







public function unionAll($query)
{
return $this->union($query, true);
}







public function lock($value = true)
{
$this->lock = $value;

if (! is_null($this->lock)) {
$this->useWritePdo();
}

return $this;
}






public function lockForUpdate()
{
return $this->lock(true);
}






public function sharedLock()
{
return $this->lock(false);
}







public function beforeQuery(callable $callback)
{
$this->beforeQueryCallbacks[] = $callback;

return $this;
}






public function applyBeforeQueryCallbacks()
{
foreach ($this->beforeQueryCallbacks as $callback) {
$callback($this);
}

$this->beforeQueryCallbacks = [];
}







public function afterQuery(Closure $callback)
{
$this->afterQueryCallbacks[] = $callback;

return $this;
}







public function applyAfterQueryCallbacks($result)
{
foreach ($this->afterQueryCallbacks as $afterQueryCallback) {
$result = $afterQueryCallback($result) ?: $result;
}

return $result;
}






public function toSql()
{
$this->applyBeforeQueryCallbacks();

return $this->grammar->compileSelect($this);
}






public function toRawSql()
{
return $this->grammar->substituteBindingsIntoRawSql(
$this->toSql(), $this->connection->prepareBindings($this->getBindings())
);
}








public function find($id, $columns = ['*'])
{
return $this->where('id', '=', $id)->first($columns);
}

/**
@template







*/
public function findOr($id, $columns = ['*'], ?Closure $callback = null)
{
if ($columns instanceof Closure) {
$callback = $columns;

$columns = ['*'];
}

if (! is_null($data = $this->find($id, $columns))) {
return $data;
}

return $callback();
}







public function value($column)
{
$result = (array) $this->first([$column]);

return count($result) > 0 ? reset($result) : null;
}








public function rawValue(string $expression, array $bindings = [])
{
$result = (array) $this->selectRaw($expression, $bindings)->first();

return count($result) > 0 ? reset($result) : null;
}










public function soleValue($column)
{
$result = (array) $this->sole([$column]);

return reset($result);
}







public function get($columns = ['*'])
{
$items = collect($this->onceWithColumns(Arr::wrap($columns), function () {
return $this->processor->processSelect($this, $this->runSelect());
}));

return $this->applyAfterQueryCallbacks(
isset($this->groupLimit) ? $this->withoutGroupLimitKeys($items) : $items
);
}






protected function runSelect()
{
return $this->connection->select(
$this->toSql(), $this->getBindings(), ! $this->useWritePdo
);
}







protected function withoutGroupLimitKeys($items)
{
$keysToRemove = ['laravel_row'];

if (is_string($this->groupLimit['column'])) {
$column = last(explode('.', $this->groupLimit['column']));

$keysToRemove[] = '@laravel_group := '.$this->grammar->wrap($column);
$keysToRemove[] = '@laravel_group := '.$this->grammar->wrap('pivot_'.$column);
}

$items->each(function ($item) use ($keysToRemove) {
foreach ($keysToRemove as $key) {
unset($item->$key);
}
});

return $items;
}











public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null, $total = null)
{
$page = $page ?: Paginator::resolveCurrentPage($pageName);

$total = value($total) ?? $this->getCountForPagination();

$perPage = $perPage instanceof Closure ? $perPage($total) : $perPage;

$results = $total ? $this->forPage($page, $perPage)->get($columns) : collect();

return $this->paginator($results, $total, $perPage, $page, [
'path' => Paginator::resolveCurrentPath(),
'pageName' => $pageName,
]);
}












public function simplePaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
{
$page = $page ?: Paginator::resolveCurrentPage($pageName);

$this->offset(($page - 1) * $perPage)->limit($perPage + 1);

return $this->simplePaginator($this->get($columns), $perPage, $page, [
'path' => Paginator::resolveCurrentPath(),
'pageName' => $pageName,
]);
}












public function cursorPaginate($perPage = 15, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
{
return $this->paginateUsingCursor($perPage, $columns, $cursorName, $cursor);
}







protected function ensureOrderForCursorPagination($shouldReverse = false)
{
if (empty($this->orders) && empty($this->unionOrders)) {
$this->enforceOrderBy();
}

$reverseDirection = function ($order) {
if (! isset($order['direction'])) {
return $order;
}

$order['direction'] = $order['direction'] === 'asc' ? 'desc' : 'asc';

return $order;
};

if ($shouldReverse) {
$this->orders = collect($this->orders)->map($reverseDirection)->toArray();
$this->unionOrders = collect($this->unionOrders)->map($reverseDirection)->toArray();
}

$orders = ! empty($this->unionOrders) ? $this->unionOrders : $this->orders;

return collect($orders)
->filter(fn ($order) => Arr::has($order, 'direction'))
->values();
}







public function getCountForPagination($columns = ['*'])
{
$results = $this->runPaginationCountQuery($columns);




if (! isset($results[0])) {
return 0;
} elseif (is_object($results[0])) {
return (int) $results[0]->aggregate;
}

return (int) array_change_key_case((array) $results[0])['aggregate'];
}







protected function runPaginationCountQuery($columns = ['*'])
{
if ($this->groups || $this->havings) {
$clone = $this->cloneForPaginationCount();

if (is_null($clone->columns) && ! empty($this->joins)) {
$clone->select($this->from.'.*');
}

return $this->newQuery()
->from(new Expression('('.$clone->toSql().') as '.$this->grammar->wrap('aggregate_table')))
->mergeBindings($clone)
->setAggregate('count', $this->withoutSelectAliases($columns))
->get()->all();
}

$without = $this->unions ? ['unionOrders', 'unionLimit', 'unionOffset'] : ['columns', 'orders', 'limit', 'offset'];

return $this->cloneWithout($without)
->cloneWithoutBindings($this->unions ? ['unionOrder'] : ['select', 'order'])
->setAggregate('count', $this->withoutSelectAliases($columns))
->get()->all();
}






protected function cloneForPaginationCount()
{
return $this->cloneWithout(['orders', 'limit', 'offset'])
->cloneWithoutBindings(['order']);
}







protected function withoutSelectAliases(array $columns)
{
return array_map(function ($column) {
return is_string($column) && ($aliasPosition = stripos($column, ' as ')) !== false
? substr($column, 0, $aliasPosition) : $column;
}, $columns);
}






public function cursor()
{
if (is_null($this->columns)) {
$this->columns = ['*'];
}

return (new LazyCollection(function () {
yield from $this->connection->cursor(
$this->toSql(), $this->getBindings(), ! $this->useWritePdo
);
}))->map(function ($item) {
return $this->applyAfterQueryCallbacks(collect([$item]))->first();
})->reject(fn ($item) => is_null($item));
}








protected function enforceOrderBy()
{
if (empty($this->orders) && empty($this->unionOrders)) {
throw new RuntimeException('You must specify an orderBy clause when using this function.');
}
}








public function pluck($column, $key = null)
{



$queryResult = $this->onceWithColumns(
is_null($key) ? [$column] : [$column, $key],
function () {
return $this->processor->processSelect(
$this, $this->runSelect()
);
}
);

if (empty($queryResult)) {
return collect();
}




$column = $this->stripTableForPluck($column);

$key = $this->stripTableForPluck($key);

return $this->applyAfterQueryCallbacks(
is_array($queryResult[0])
? $this->pluckFromArrayColumn($queryResult, $column, $key)
: $this->pluckFromObjectColumn($queryResult, $column, $key)
);
}







protected function stripTableForPluck($column)
{
if (is_null($column)) {
return $column;
}

$columnString = $column instanceof ExpressionContract
? $this->grammar->getValue($column)
: $column;

$separator = str_contains(strtolower($columnString), ' as ') ? ' as ' : '\.';

return last(preg_split('~'.$separator.'~i', $columnString));
}









protected function pluckFromObjectColumn($queryResult, $column, $key)
{
$results = [];

if (is_null($key)) {
foreach ($queryResult as $row) {
$results[] = $row->$column;
}
} else {
foreach ($queryResult as $row) {
$results[$row->$key] = $row->$column;
}
}

return collect($results);
}









protected function pluckFromArrayColumn($queryResult, $column, $key)
{
$results = [];

if (is_null($key)) {
foreach ($queryResult as $row) {
$results[] = $row[$column];
}
} else {
foreach ($queryResult as $row) {
$results[$row[$key]] = $row[$column];
}
}

return collect($results);
}








public function implode($column, $glue = '')
{
return $this->pluck($column)->implode($glue);
}






public function exists()
{
$this->applyBeforeQueryCallbacks();

$results = $this->connection->select(
$this->grammar->compileExists($this), $this->getBindings(), ! $this->useWritePdo
);




if (isset($results[0])) {
$results = (array) $results[0];

return (bool) $results['exists'];
}

return false;
}






public function doesntExist()
{
return ! $this->exists();
}







public function existsOr(Closure $callback)
{
return $this->exists() ? true : $callback();
}







public function doesntExistOr(Closure $callback)
{
return $this->doesntExist() ? true : $callback();
}







public function count($columns = '*')
{
return (int) $this->aggregate(__FUNCTION__, Arr::wrap($columns));
}







public function min($column)
{
return $this->aggregate(__FUNCTION__, [$column]);
}







public function max($column)
{
return $this->aggregate(__FUNCTION__, [$column]);
}







public function sum($column)
{
$result = $this->aggregate(__FUNCTION__, [$column]);

return $result ?: 0;
}







public function avg($column)
{
return $this->aggregate(__FUNCTION__, [$column]);
}







public function average($column)
{
return $this->avg($column);
}








public function aggregate($function, $columns = ['*'])
{
$results = $this->cloneWithout($this->unions || $this->havings ? [] : ['columns'])
->cloneWithoutBindings($this->unions || $this->havings ? [] : ['select'])
->setAggregate($function, $columns)
->get($columns);

if (! $results->isEmpty()) {
return array_change_key_case((array) $results[0])['aggregate'];
}
}








public function numericAggregate($function, $columns = ['*'])
{
$result = $this->aggregate($function, $columns);




if (! $result) {
return 0;
}

if (is_int($result) || is_float($result)) {
return $result;
}




return ! str_contains((string) $result, '.')
? (int) $result : (float) $result;
}








protected function setAggregate($function, $columns)
{
$this->aggregate = compact('function', 'columns');

if (empty($this->groups)) {
$this->orders = null;

$this->bindings['order'] = [];
}

return $this;
}










protected function onceWithColumns($columns, $callback)
{
$original = $this->columns;

if (is_null($original)) {
$this->columns = $columns;
}

$result = $callback();

$this->columns = $original;

return $result;
}







public function insert(array $values)
{



if (empty($values)) {
return true;
}

if (! is_array(reset($values))) {
$values = [$values];
}




else {
foreach ($values as $key => $value) {
ksort($value);

$values[$key] = $value;
}
}

$this->applyBeforeQueryCallbacks();




return $this->connection->insert(
$this->grammar->compileInsert($this, $values),
$this->cleanBindings(Arr::flatten($values, 1))
);
}







public function insertOrIgnore(array $values)
{
if (empty($values)) {
return 0;
}

if (! is_array(reset($values))) {
$values = [$values];
} else {
foreach ($values as $key => $value) {
ksort($value);

$values[$key] = $value;
}
}

$this->applyBeforeQueryCallbacks();

return $this->connection->affectingStatement(
$this->grammar->compileInsertOrIgnore($this, $values),
$this->cleanBindings(Arr::flatten($values, 1))
);
}








public function insertGetId(array $values, $sequence = null)
{
$this->applyBeforeQueryCallbacks();

$sql = $this->grammar->compileInsertGetId($this, $values, $sequence);

$values = $this->cleanBindings($values);

return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
}








public function insertUsing(array $columns, $query)
{
$this->applyBeforeQueryCallbacks();

[$sql, $bindings] = $this->createSub($query);

return $this->connection->affectingStatement(
$this->grammar->compileInsertUsing($this, $columns, $sql),
$this->cleanBindings($bindings)
);
}








public function insertOrIgnoreUsing(array $columns, $query)
{
$this->applyBeforeQueryCallbacks();

[$sql, $bindings] = $this->createSub($query);

return $this->connection->affectingStatement(
$this->grammar->compileInsertOrIgnoreUsing($this, $columns, $sql),
$this->cleanBindings($bindings)
);
}







public function update(array $values)
{
$this->applyBeforeQueryCallbacks();

$values = collect($values)->map(function ($value) {
if (! $value instanceof Builder) {
return ['value' => $value, 'bindings' => match (true) {
$value instanceof Collection => $value->all(),
$value instanceof UnitEnum => enum_value($value),
default => $value,
}];
}

[$query, $bindings] = $this->parseSub($value);

return ['value' => new Expression("({$query})"), 'bindings' => fn () => $bindings];
});

$sql = $this->grammar->compileUpdate($this, $values->map(fn ($value) => $value['value'])->all());

return $this->connection->update($sql, $this->cleanBindings(
$this->grammar->prepareBindingsForUpdate($this->bindings, $values->map(fn ($value) => $value['bindings'])->all())
));
}







public function updateFrom(array $values)
{
if (! method_exists($this->grammar, 'compileUpdateFrom')) {
throw new LogicException('This database engine does not support the updateFrom method.');
}

$this->applyBeforeQueryCallbacks();

$sql = $this->grammar->compileUpdateFrom($this, $values);

return $this->connection->update($sql, $this->cleanBindings(
$this->grammar->prepareBindingsForUpdateFrom($this->bindings, $values)
));
}








public function updateOrInsert(array $attributes, array|callable $values = [])
{
$exists = $this->where($attributes)->exists();

if ($values instanceof Closure) {
$values = $values($exists);
}

if (! $exists) {
return $this->insert(array_merge($attributes, $values));
}

if (empty($values)) {
return true;
}

return (bool) $this->limit(1)->update($values);
}









public function upsert(array $values, $uniqueBy, $update = null)
{
if (empty($values)) {
return 0;
} elseif ($update === []) {
return (int) $this->insert($values);
}

if (! is_array(reset($values))) {
$values = [$values];
} else {
foreach ($values as $key => $value) {
ksort($value);

$values[$key] = $value;
}
}

if (is_null($update)) {
$update = array_keys(reset($values));
}

$this->applyBeforeQueryCallbacks();

$bindings = $this->cleanBindings(array_merge(
Arr::flatten($values, 1),
collect($update)->reject(function ($value, $key) {
return is_int($key);
})->all()
));

return $this->connection->affectingStatement(
$this->grammar->compileUpsert($this, $values, (array) $uniqueBy, $update),
$bindings
);
}











public function increment($column, $amount = 1, array $extra = [])
{
if (! is_numeric($amount)) {
throw new InvalidArgumentException('Non-numeric value passed to increment method.');
}

return $this->incrementEach([$column => $amount], $extra);
}










public function incrementEach(array $columns, array $extra = [])
{
foreach ($columns as $column => $amount) {
if (! is_numeric($amount)) {
throw new InvalidArgumentException("Non-numeric value passed as increment amount for column: '$column'.");
} elseif (! is_string($column)) {
throw new InvalidArgumentException('Non-associative array passed to incrementEach method.');
}

$columns[$column] = $this->raw("{$this->grammar->wrap($column)} + $amount");
}

return $this->update(array_merge($columns, $extra));
}











public function decrement($column, $amount = 1, array $extra = [])
{
if (! is_numeric($amount)) {
throw new InvalidArgumentException('Non-numeric value passed to decrement method.');
}

return $this->decrementEach([$column => $amount], $extra);
}










public function decrementEach(array $columns, array $extra = [])
{
foreach ($columns as $column => $amount) {
if (! is_numeric($amount)) {
throw new InvalidArgumentException("Non-numeric value passed as decrement amount for column: '$column'.");
} elseif (! is_string($column)) {
throw new InvalidArgumentException('Non-associative array passed to decrementEach method.');
}

$columns[$column] = $this->raw("{$this->grammar->wrap($column)} - $amount");
}

return $this->update(array_merge($columns, $extra));
}







public function delete($id = null)
{



if (! is_null($id)) {
$this->where($this->from.'.id', '=', $id);
}

$this->applyBeforeQueryCallbacks();

return $this->connection->delete(
$this->grammar->compileDelete($this), $this->cleanBindings(
$this->grammar->prepareBindingsForDelete($this->bindings)
)
);
}






public function truncate()
{
$this->applyBeforeQueryCallbacks();

foreach ($this->grammar->compileTruncate($this) as $sql => $bindings) {
$this->connection->statement($sql, $bindings);
}
}






public function newQuery()
{
return new static($this->connection, $this->grammar, $this->processor);
}






protected function forSubQuery()
{
return $this->newQuery();
}






public function getColumns()
{
return ! is_null($this->columns)
? array_map(fn ($column) => $this->grammar->getValue($column), $this->columns)
: [];
}







public function raw($value)
{
return $this->connection->raw($value);
}






protected function getUnionBuilders()
{
return isset($this->unions)
? collect($this->unions)->pluck('query')
: collect();
}






public function getBindings()
{
return Arr::flatten($this->bindings);
}






public function getRawBindings()
{
return $this->bindings;
}










public function setBindings(array $bindings, $type = 'where')
{
if (! array_key_exists($type, $this->bindings)) {
throw new InvalidArgumentException("Invalid binding type: {$type}.");
}

$this->bindings[$type] = $bindings;

return $this;
}










public function addBinding($value, $type = 'where')
{
if (! array_key_exists($type, $this->bindings)) {
throw new InvalidArgumentException("Invalid binding type: {$type}.");
}

if (is_array($value)) {
$this->bindings[$type] = array_values(array_map(
[$this, 'castBinding'],
array_merge($this->bindings[$type], $value),
));
} else {
$this->bindings[$type][] = $this->castBinding($value);
}

return $this;
}







public function castBinding($value)
{
if ($value instanceof UnitEnum) {
return enum_value($value);
}

return $value;
}







public function mergeBindings(self $query)
{
$this->bindings = array_merge_recursive($this->bindings, $query->bindings);

return $this;
}







public function cleanBindings(array $bindings)
{
return collect($bindings)
->reject(function ($binding) {
return $binding instanceof ExpressionContract;
})
->map([$this, 'castBinding'])
->values()
->all();
}







protected function flattenValue($value)
{
return is_array($value) ? head(Arr::flatten($value)) : $value;
}






protected function defaultKeyName()
{
return 'id';
}






public function getConnection()
{
return $this->connection;
}






public function getProcessor()
{
return $this->processor;
}






public function getGrammar()
{
return $this->grammar;
}






public function useWritePdo()
{
$this->useWritePdo = true;

return $this;
}







protected function isQueryable($value)
{
return $value instanceof self ||
$value instanceof EloquentBuilder ||
$value instanceof Relation ||
$value instanceof Closure;
}






public function clone()
{
return clone $this;
}







public function cloneWithout(array $properties)
{
return tap($this->clone(), function ($clone) use ($properties) {
foreach ($properties as $property) {
$clone->{$property} = null;
}
});
}







public function cloneWithoutBindings(array $except)
{
return tap($this->clone(), function ($clone) use ($except) {
foreach ($except as $type) {
$clone->bindings[$type] = [];
}
});
}







public function dump(...$args)
{
dump(
$this->toSql(),
$this->getBindings(),
...$args,
);

return $this;
}






public function dumpRawSql()
{
dump($this->toRawSql());

return $this;
}






public function dd()
{
dd($this->toSql(), $this->getBindings());
}






public function ddRawSql()
{
dd($this->toRawSql());
}










public function __call($method, $parameters)
{
if (static::hasMacro($method)) {
return $this->macroCall($method, $parameters);
}

if (str_starts_with($method, 'where')) {
return $this->dynamicWhere($method, $parameters);
}

static::throwBadMethodCallException($method);
}
}
