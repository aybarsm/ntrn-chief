<?php

namespace Illuminate\Database\Eloquent\Relations\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use InvalidArgumentException;

trait CanBeOneOfMany
{





protected $isOneOfMany = false;






protected $relationName;






protected $oneOfManySubQuery;









abstract public function addOneOfManySubQueryConstraints(Builder $query, $column = null, $aggregate = null);






abstract public function getOneOfManySubQuerySelectColumns();







abstract public function addOneOfManyJoinSubQueryConstraints(JoinClause $join);











public function ofMany($column = 'id', $aggregate = 'MAX', $relation = null)
{
$this->isOneOfMany = true;

$this->relationName = $relation ?: $this->getDefaultOneOfManyJoinAlias(
$this->guessRelationship()
);

$keyName = $this->query->getModel()->getKeyName();

$columns = is_string($columns = $column) ? [
$column => $aggregate,
$keyName => $aggregate,
] : $column;

if (! array_key_exists($keyName, $columns)) {
$columns[$keyName] = 'MAX';
}

if ($aggregate instanceof Closure) {
$closure = $aggregate;
}

foreach ($columns as $column => $aggregate) {
if (! in_array(strtolower($aggregate), ['min', 'max'])) {
throw new InvalidArgumentException("Invalid aggregate [{$aggregate}] used within ofMany relation. Available aggregates: MIN, MAX");
}

$subQuery = $this->newOneOfManySubQuery(
$this->getOneOfManySubQuerySelectColumns(),
array_merge([$column], $previous['columns'] ?? []),
$aggregate,
);

if (isset($previous)) {
$this->addOneOfManyJoinSubQuery(
$subQuery,
$previous['subQuery'],
$previous['columns'],
);
}

if (isset($closure)) {
$closure($subQuery);
}

if (! isset($previous)) {
$this->oneOfManySubQuery = $subQuery;
}

if (array_key_last($columns) == $column) {
$this->addOneOfManyJoinSubQuery(
$this->query,
$subQuery,
array_merge([$column], $previous['columns'] ?? []),
);
}

$previous = [
'subQuery' => $subQuery,
'columns' => array_merge([$column], $previous['columns'] ?? []),
];
}

$this->addConstraints();

$columns = $this->query->getQuery()->columns;

if (is_null($columns) || $columns === ['*']) {
$this->select([$this->qualifyColumn('*')]);
}

return $this;
}








public function latestOfMany($column = 'id', $relation = null)
{
return $this->ofMany(collect(Arr::wrap($column))->mapWithKeys(function ($column) {
return [$column => 'MAX'];
})->all(), 'MAX', $relation);
}








public function oldestOfMany($column = 'id', $relation = null)
{
return $this->ofMany(collect(Arr::wrap($column))->mapWithKeys(function ($column) {
return [$column => 'MIN'];
})->all(), 'MIN', $relation);
}







protected function getDefaultOneOfManyJoinAlias($relation)
{
return $relation == $this->query->getModel()->getTable()
? $relation.'_of_many'
: $relation;
}









protected function newOneOfManySubQuery($groupBy, $columns = null, $aggregate = null)
{
$subQuery = $this->query->getModel()
->newQuery()
->withoutGlobalScopes($this->removedScopes());

foreach (Arr::wrap($groupBy) as $group) {
$subQuery->groupBy($this->qualifyRelatedColumn($group));
}

if (! is_null($columns)) {
foreach ($columns as $key => $column) {
$aggregatedColumn = $subQuery->getQuery()->grammar->wrap($subQuery->qualifyColumn($column));

if ($key === 0) {
$aggregatedColumn = "{$aggregate}({$aggregatedColumn})";
} else {
$aggregatedColumn = "min({$aggregatedColumn})";
}

$subQuery->selectRaw($aggregatedColumn.' as '.$subQuery->getQuery()->grammar->wrap($column.'_aggregate'));
}
}

$this->addOneOfManySubQueryConstraints($subQuery, column: null, aggregate: $aggregate);

return $subQuery;
}









protected function addOneOfManyJoinSubQuery(Builder $parent, Builder $subQuery, $on)
{
$parent->beforeQuery(function ($parent) use ($subQuery, $on) {
$subQuery->applyBeforeQueryCallbacks();

$parent->joinSub($subQuery, $this->relationName, function ($join) use ($on) {
foreach ($on as $onColumn) {
$join->on($this->qualifySubSelectColumn($onColumn.'_aggregate'), '=', $this->qualifyRelatedColumn($onColumn));
}

$this->addOneOfManyJoinSubQueryConstraints($join);
});
});
}







protected function mergeOneOfManyJoinsTo(Builder $query)
{
$query->getQuery()->beforeQueryCallbacks = $this->query->getQuery()->beforeQueryCallbacks;

$query->applyBeforeQueryCallbacks();
}






protected function getRelationQuery()
{
return $this->isOneOfMany()
? $this->oneOfManySubQuery
: $this->query;
}






public function getOneOfManySubQuery()
{
return $this->oneOfManySubQuery;
}







public function qualifySubSelectColumn($column)
{
return $this->getRelationName().'.'.last(explode('.', $column));
}







protected function qualifyRelatedColumn($column)
{
return str_contains($column, '.') ? $column : $this->query->getModel()->getTable().'.'.$column;
}






protected function guessRelationship()
{
return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];
}






public function isOneOfMany()
{
return $this->isOneOfMany;
}






public function getRelationName()
{
return $this->relationName;
}
}
