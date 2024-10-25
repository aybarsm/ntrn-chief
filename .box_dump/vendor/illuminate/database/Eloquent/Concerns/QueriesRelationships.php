<?php

namespace Illuminate\Database\Eloquent\Concerns;

use BadMethodCallException;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
@mixin */
trait QueriesRelationships
{












public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', ?Closure $callback = null)
{
if (is_string($relation)) {
if (str_contains($relation, '.')) {
return $this->hasNested($relation, $operator, $count, $boolean, $callback);
}

$relation = $this->getRelationWithoutConstraints($relation);
}

if ($relation instanceof MorphTo) {
return $this->hasMorph($relation, ['*'], $operator, $count, $boolean, $callback);
}




$method = $this->canUseExistsForExistenceCheck($operator, $count)
? 'getRelationExistenceQuery'
: 'getRelationExistenceCountQuery';

$hasQuery = $relation->{$method}(
$relation->getRelated()->newQueryWithoutRelationships(), $this
);




if ($callback) {
$hasQuery->callScope($callback);
}

return $this->addHasWhere(
$hasQuery, $relation, $operator, $count, $boolean
);
}













protected function hasNested($relations, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
{
$relations = explode('.', $relations);

$doesntHave = $operator === '<' && $count === 1;

if ($doesntHave) {
$operator = '>=';
$count = 1;
}

$closure = function ($q) use (&$closure, &$relations, $operator, $count, $callback) {



count($relations) > 1
? $q->whereHas(array_shift($relations), $closure)
: $q->has(array_shift($relations), $operator, $count, 'and', $callback);
};

return $this->has(array_shift($relations), $doesntHave ? '<' : '>=', 1, $boolean, $closure);
}









public function orHas($relation, $operator = '>=', $count = 1)
{
return $this->has($relation, $operator, $count, 'or');
}









public function doesntHave($relation, $boolean = 'and', ?Closure $callback = null)
{
return $this->has($relation, '<', 1, $boolean, $callback);
}







public function orDoesntHave($relation)
{
return $this->doesntHave($relation, 'or');
}










public function whereHas($relation, ?Closure $callback = null, $operator = '>=', $count = 1)
{
return $this->has($relation, $operator, $count, 'and', $callback);
}












public function withWhereHas($relation, ?Closure $callback = null, $operator = '>=', $count = 1)
{
return $this->whereHas(Str::before($relation, ':'), $callback, $operator, $count)
->with($callback ? [$relation => fn ($query) => $callback($query)] : $relation);
}










public function orWhereHas($relation, ?Closure $callback = null, $operator = '>=', $count = 1)
{
return $this->has($relation, $operator, $count, 'or', $callback);
}








public function whereDoesntHave($relation, ?Closure $callback = null)
{
return $this->doesntHave($relation, 'and', $callback);
}








public function orWhereDoesntHave($relation, ?Closure $callback = null)
{
return $this->doesntHave($relation, 'or', $callback);
}












public function hasMorph($relation, $types, $operator = '>=', $count = 1, $boolean = 'and', ?Closure $callback = null)
{
if (is_string($relation)) {
$relation = $this->getRelationWithoutConstraints($relation);
}

$types = (array) $types;

if ($types === ['*']) {
$types = $this->model->newModelQuery()->distinct()->pluck($relation->getMorphType())->filter()->all();
}

if (empty($types)) {
return $this->where(new Expression('0'), $operator, $count, $boolean);
}

foreach ($types as &$type) {
$type = Relation::getMorphedModel($type) ?? $type;
}

return $this->where(function ($query) use ($relation, $callback, $operator, $count, $types) {
foreach ($types as $type) {
$query->orWhere(function ($query) use ($relation, $callback, $operator, $count, $type) {
$belongsTo = $this->getBelongsToRelation($relation, $type);

if ($callback) {
$callback = function ($query) use ($callback, $type) {
return $callback($query, $type);
};
}

$query->where($this->qualifyColumn($relation->getMorphType()), '=', (new $type)->getMorphClass())
->whereHas($belongsTo, $callback, $operator, $count);
});
}
}, null, null, $boolean);
}

/**
@template
@template






*/
protected function getBelongsToRelation(MorphTo $relation, $type)
{
$belongsTo = Relation::noConstraints(function () use ($relation, $type) {
return $this->model->belongsTo(
$type,
$relation->getForeignKeyName(),
$relation->getOwnerKeyName()
);
});

$belongsTo->getQuery()->mergeConstraintsFrom($relation->getQuery());

return $belongsTo;
}










public function orHasMorph($relation, $types, $operator = '>=', $count = 1)
{
return $this->hasMorph($relation, $types, $operator, $count, 'or');
}










public function doesntHaveMorph($relation, $types, $boolean = 'and', ?Closure $callback = null)
{
return $this->hasMorph($relation, $types, '<', 1, $boolean, $callback);
}








public function orDoesntHaveMorph($relation, $types)
{
return $this->doesntHaveMorph($relation, $types, 'or');
}











public function whereHasMorph($relation, $types, ?Closure $callback = null, $operator = '>=', $count = 1)
{
return $this->hasMorph($relation, $types, $operator, $count, 'and', $callback);
}











public function orWhereHasMorph($relation, $types, ?Closure $callback = null, $operator = '>=', $count = 1)
{
return $this->hasMorph($relation, $types, $operator, $count, 'or', $callback);
}









public function whereDoesntHaveMorph($relation, $types, ?Closure $callback = null)
{
return $this->doesntHaveMorph($relation, $types, 'and', $callback);
}









public function orWhereDoesntHaveMorph($relation, $types, ?Closure $callback = null)
{
return $this->doesntHaveMorph($relation, $types, 'or', $callback);
}










public function whereRelation($relation, $column, $operator = null, $value = null)
{
return $this->whereHas($relation, function ($query) use ($column, $operator, $value) {
if ($column instanceof Closure) {
$column($query);
} else {
$query->where($column, $operator, $value);
}
});
}










public function orWhereRelation($relation, $column, $operator = null, $value = null)
{
return $this->orWhereHas($relation, function ($query) use ($column, $operator, $value) {
if ($column instanceof Closure) {
$column($query);
} else {
$query->where($column, $operator, $value);
}
});
}











public function whereMorphRelation($relation, $types, $column, $operator = null, $value = null)
{
return $this->whereHasMorph($relation, $types, function ($query) use ($column, $operator, $value) {
$query->where($column, $operator, $value);
});
}











public function orWhereMorphRelation($relation, $types, $column, $operator = null, $value = null)
{
return $this->orWhereHasMorph($relation, $types, function ($query) use ($column, $operator, $value) {
$query->where($column, $operator, $value);
});
}








public function whereMorphedTo($relation, $model, $boolean = 'and')
{
if (is_string($relation)) {
$relation = $this->getRelationWithoutConstraints($relation);
}

if (is_null($model)) {
return $this->whereNull($relation->qualifyColumn($relation->getMorphType()), $boolean);
}

if (is_string($model)) {
$morphMap = Relation::morphMap();

if (! empty($morphMap) && in_array($model, $morphMap)) {
$model = array_search($model, $morphMap, true);
}

return $this->where($relation->qualifyColumn($relation->getMorphType()), $model, null, $boolean);
}

return $this->where(function ($query) use ($relation, $model) {
$query->where($relation->qualifyColumn($relation->getMorphType()), $model->getMorphClass())
->where($relation->qualifyColumn($relation->getForeignKeyName()), $model->getKey());
}, null, null, $boolean);
}








public function whereNotMorphedTo($relation, $model, $boolean = 'and')
{
if (is_string($relation)) {
$relation = $this->getRelationWithoutConstraints($relation);
}

if (is_string($model)) {
$morphMap = Relation::morphMap();

if (! empty($morphMap) && in_array($model, $morphMap)) {
$model = array_search($model, $morphMap, true);
}

return $this->whereNot($relation->qualifyColumn($relation->getMorphType()), '<=>', $model, $boolean);
}

return $this->whereNot(function ($query) use ($relation, $model) {
$query->where($relation->qualifyColumn($relation->getMorphType()), '<=>', $model->getMorphClass())
->where($relation->qualifyColumn($relation->getForeignKeyName()), '<=>', $model->getKey());
}, null, null, $boolean);
}








public function orWhereMorphedTo($relation, $model)
{
return $this->whereMorphedTo($relation, $model, 'or');
}








public function orWhereNotMorphedTo($relation, $model)
{
return $this->whereNotMorphedTo($relation, $model, 'or');
}











public function whereBelongsTo($related, $relationshipName = null, $boolean = 'and')
{
if (! $related instanceof Collection) {
$relatedCollection = $related->newCollection([$related]);
} else {
$relatedCollection = $related;

$related = $relatedCollection->first();
}

if ($relatedCollection->isEmpty()) {
throw new InvalidArgumentException('Collection given to whereBelongsTo method may not be empty.');
}

if ($relationshipName === null) {
$relationshipName = Str::camel(class_basename($related));
}

try {
$relationship = $this->model->{$relationshipName}();
} catch (BadMethodCallException) {
throw RelationNotFoundException::make($this->model, $relationshipName);
}

if (! $relationship instanceof BelongsTo) {
throw RelationNotFoundException::make($this->model, $relationshipName, BelongsTo::class);
}

$this->whereIn(
$relationship->getQualifiedForeignKeyName(),
$relatedCollection->pluck($relationship->getOwnerKeyName())->toArray(),
$boolean,
);

return $this;
}










public function orWhereBelongsTo($related, $relationshipName = null)
{
return $this->whereBelongsTo($related, $relationshipName, 'or');
}









public function withAggregate($relations, $column, $function = null)
{
if (empty($relations)) {
return $this;
}

if (is_null($this->query->columns)) {
$this->query->select([$this->query->from.'.*']);
}

$relations = is_array($relations) ? $relations : [$relations];

foreach ($this->parseWithRelations($relations) as $name => $constraints) {



$segments = explode(' ', $name);

unset($alias);

if (count($segments) === 3 && Str::lower($segments[1]) === 'as') {
[$name, $alias] = [$segments[0], $segments[2]];
}

$relation = $this->getRelationWithoutConstraints($name);

if ($function) {
if ($this->getQuery()->getGrammar()->isExpression($column)) {
$aggregateColumn = $this->getQuery()->getGrammar()->getValue($column);
} else {
$hashedColumn = $this->getRelationHashedColumn($column, $relation);

$aggregateColumn = $this->getQuery()->getGrammar()->wrap(
$column === '*' ? $column : $relation->getRelated()->qualifyColumn($hashedColumn)
);
}

$expression = $function === 'exists' ? $aggregateColumn : sprintf('%s(%s)', $function, $aggregateColumn);
} else {
$expression = $this->getQuery()->getGrammar()->getValue($column);
}




$query = $relation->getRelationExistenceQuery(
$relation->getRelated()->newQuery(), $this, new Expression($expression)
)->setBindings([], 'select');

$query->callScope($constraints);

$query = $query->mergeConstraintsFrom($relation->getQuery())->toBase();




$query->orders = null;
$query->setBindings([], 'order');

if (count($query->columns) > 1) {
$query->columns = [$query->columns[0]];
$query->bindings['select'] = [];
}




$alias ??= Str::snake(
preg_replace('/[^[:alnum:][:space:]_]/u', '', "$name $function {$this->getQuery()->getGrammar()->getValue($column)}")
);

if ($function === 'exists') {
$this->selectRaw(
sprintf('exists(%s) as %s', $query->toSql(), $this->getQuery()->grammar->wrap($alias)),
$query->getBindings()
)->withCasts([$alias => 'bool']);
} else {
$this->selectSub(
$function ? $query : $query->limit(1),
$alias
);
}
}

return $this;
}








protected function getRelationHashedColumn($column, $relation)
{
if (str_contains($column, '.')) {
return $column;
}

return $this->getQuery()->from === $relation->getQuery()->getQuery()->from
? "{$relation->getRelationCountHash(false)}.$column"
: $column;
}







public function withCount($relations)
{
return $this->withAggregate(is_array($relations) ? $relations : func_get_args(), '*', 'count');
}








public function withMax($relation, $column)
{
return $this->withAggregate($relation, $column, 'max');
}








public function withMin($relation, $column)
{
return $this->withAggregate($relation, $column, 'min');
}








public function withSum($relation, $column)
{
return $this->withAggregate($relation, $column, 'sum');
}








public function withAvg($relation, $column)
{
return $this->withAggregate($relation, $column, 'avg');
}







public function withExists($relation)
{
return $this->withAggregate($relation, '*', 'exists');
}











protected function addHasWhere(Builder $hasQuery, Relation $relation, $operator, $count, $boolean)
{
$hasQuery->mergeConstraintsFrom($relation->getQuery());

return $this->canUseExistsForExistenceCheck($operator, $count)
? $this->addWhereExistsQuery($hasQuery->toBase(), $boolean, $operator === '<' && $count === 1)
: $this->addWhereCountQuery($hasQuery->toBase(), $operator, $count, $boolean);
}







public function mergeConstraintsFrom(Builder $from)
{
$whereBindings = $from->getQuery()->getRawBindings()['where'] ?? [];

$wheres = $from->getQuery()->from !== $this->getQuery()->from
? $this->requalifyWhereTables(
$from->getQuery()->wheres,
$from->getQuery()->grammar->getValue($from->getQuery()->from),
$this->getModel()->getTable()
) : $from->getQuery()->wheres;




return $this->withoutGlobalScopes(
$from->removedScopes()
)->mergeWheres(
$wheres, $whereBindings
);
}









protected function requalifyWhereTables(array $wheres, string $from, string $to): array
{
return collect($wheres)->map(function ($where) use ($from, $to) {
return collect($where)->map(function ($value) use ($from, $to) {
return is_string($value) && str_starts_with($value, $from.'.')
? $to.'.'.Str::afterLast($value, '.')
: $value;
});
})->toArray();
}










protected function addWhereCountQuery(QueryBuilder $query, $operator = '>=', $count = 1, $boolean = 'and')
{
$this->query->addBinding($query->getBindings(), 'where');

return $this->where(
new Expression('('.$query->toSql().')'),
$operator,
is_numeric($count) ? new Expression($count) : $count,
$boolean
);
}







protected function getRelationWithoutConstraints($relation)
{
return Relation::noConstraints(function () use ($relation) {
return $this->getModel()->{$relation}();
});
}








protected function canUseExistsForExistenceCheck($operator, $count)
{
return ($operator === '>=' || $operator === '<') && $count === 1;
}
}
