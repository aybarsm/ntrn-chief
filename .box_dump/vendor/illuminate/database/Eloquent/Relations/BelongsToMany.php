<?php

namespace Illuminate\Database\Eloquent\Relations;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithPivotTable;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
@template
@template
@extends

*/
class BelongsToMany extends Relation
{
use InteractsWithDictionary, InteractsWithPivotTable;






protected $table;






protected $foreignPivotKey;






protected $relatedPivotKey;






protected $parentKey;






protected $relatedKey;






protected $relationName;






protected $pivotColumns = [];






protected $pivotWheres = [];






protected $pivotWhereIns = [];






protected $pivotWhereNulls = [];






protected $pivotValues = [];






public $withTimestamps = false;






protected $pivotCreatedAt;






protected $pivotUpdatedAt;






protected $using;






protected $accessor = 'pivot';














public function __construct(Builder $query, Model $parent, $table, $foreignPivotKey,
$relatedPivotKey, $parentKey, $relatedKey, $relationName = null)
{
$this->parentKey = $parentKey;
$this->relatedKey = $relatedKey;
$this->relationName = $relationName;
$this->relatedPivotKey = $relatedPivotKey;
$this->foreignPivotKey = $foreignPivotKey;
$this->table = $this->resolveTableName($table);

parent::__construct($query, $parent);
}







protected function resolveTableName($table)
{
if (! str_contains($table, '\\') || ! class_exists($table)) {
return $table;
}

$model = new $table;

if (! $model instanceof Model) {
return $table;
}

if (in_array(AsPivot::class, class_uses_recursive($model))) {
$this->using($table);
}

return $model->getTable();
}






public function addConstraints()
{
$this->performJoin();

if (static::$constraints) {
$this->addWhereConstraints();
}
}







protected function performJoin($query = null)
{
$query = $query ?: $this->query;




$query->join(
$this->table,
$this->getQualifiedRelatedKeyName(),
'=',
$this->getQualifiedRelatedPivotKeyName()
);

return $this;
}






protected function addWhereConstraints()
{
$this->query->where(
$this->getQualifiedForeignPivotKeyName(), '=', $this->parent->{$this->parentKey}
);

return $this;
}


public function addEagerConstraints(array $models)
{
$whereIn = $this->whereInMethod($this->parent, $this->parentKey);

$this->whereInEager(
$whereIn,
$this->getQualifiedForeignPivotKeyName(),
$this->getKeys($models, $this->parentKey)
);
}


public function initRelation(array $models, $relation)
{
foreach ($models as $model) {
$model->setRelation($relation, $this->related->newCollection());
}

return $models;
}


public function match(array $models, Collection $results, $relation)
{
$dictionary = $this->buildDictionary($results);




foreach ($models as $model) {
$key = $this->getDictionaryKey($model->{$this->parentKey});

if (isset($dictionary[$key])) {
$model->setRelation(
$relation, $this->related->newCollection($dictionary[$key])
);
}
}

return $models;
}







protected function buildDictionary(Collection $results)
{



$dictionary = [];

foreach ($results as $result) {
$value = $this->getDictionaryKey($result->{$this->accessor}->{$this->foreignPivotKey});

$dictionary[$value][] = $result;
}

return $dictionary;
}






public function getPivotClass()
{
return $this->using ?? Pivot::class;
}







public function using($class)
{
$this->using = $class;

return $this;
}







public function as($accessor)
{
$this->accessor = $accessor;

return $this;
}










public function wherePivot($column, $operator = null, $value = null, $boolean = 'and')
{
$this->pivotWheres[] = func_get_args();

return $this->where($this->qualifyPivotColumn($column), $operator, $value, $boolean);
}










public function wherePivotBetween($column, array $values, $boolean = 'and', $not = false)
{
return $this->whereBetween($this->qualifyPivotColumn($column), $values, $boolean, $not);
}








public function orWherePivotBetween($column, array $values)
{
return $this->wherePivotBetween($column, $values, 'or');
}









public function wherePivotNotBetween($column, array $values, $boolean = 'and')
{
return $this->wherePivotBetween($column, $values, $boolean, true);
}








public function orWherePivotNotBetween($column, array $values)
{
return $this->wherePivotBetween($column, $values, 'or', true);
}










public function wherePivotIn($column, $values, $boolean = 'and', $not = false)
{
$this->pivotWhereIns[] = func_get_args();

return $this->whereIn($this->qualifyPivotColumn($column), $values, $boolean, $not);
}









public function orWherePivot($column, $operator = null, $value = null)
{
return $this->wherePivot($column, $operator, $value, 'or');
}












public function withPivotValue($column, $value = null)
{
if (is_array($column)) {
foreach ($column as $name => $value) {
$this->withPivotValue($name, $value);
}

return $this;
}

if (is_null($value)) {
throw new InvalidArgumentException('The provided value may not be null.');
}

$this->pivotValues[] = compact('column', 'value');

return $this->wherePivot($column, '=', $value);
}








public function orWherePivotIn($column, $values)
{
return $this->wherePivotIn($column, $values, 'or');
}









public function wherePivotNotIn($column, $values, $boolean = 'and')
{
return $this->wherePivotIn($column, $values, $boolean, true);
}








public function orWherePivotNotIn($column, $values)
{
return $this->wherePivotNotIn($column, $values, 'or');
}









public function wherePivotNull($column, $boolean = 'and', $not = false)
{
$this->pivotWhereNulls[] = func_get_args();

return $this->whereNull($this->qualifyPivotColumn($column), $boolean, $not);
}








public function wherePivotNotNull($column, $boolean = 'and')
{
return $this->wherePivotNull($column, $boolean, true);
}








public function orWherePivotNull($column, $not = false)
{
return $this->wherePivotNull($column, 'or', $not);
}







public function orWherePivotNotNull($column)
{
return $this->orWherePivotNull($column, true);
}








public function orderByPivot($column, $direction = 'asc')
{
return $this->orderBy($this->qualifyPivotColumn($column), $direction);
}








public function findOrNew($id, $columns = ['*'])
{
if (is_null($instance = $this->find($id, $columns))) {
$instance = $this->related->newInstance();
}

return $instance;
}








public function firstOrNew(array $attributes = [], array $values = [])
{
if (is_null($instance = $this->related->where($attributes)->first())) {
$instance = $this->related->newInstance(array_merge($attributes, $values));
}

return $instance;
}










public function firstOrCreate(array $attributes = [], array $values = [], array $joining = [], $touch = true)
{
if (is_null($instance = (clone $this)->where($attributes)->first())) {
if (is_null($instance = $this->related->where($attributes)->first())) {
$instance = $this->createOrFirst($attributes, $values, $joining, $touch);
} else {
try {
$this->getQuery()->withSavepointIfNeeded(fn () => $this->attach($instance, $joining, $touch));
} catch (UniqueConstraintViolationException) {

}
}
}

return $instance;
}










public function createOrFirst(array $attributes = [], array $values = [], array $joining = [], $touch = true)
{
try {
return $this->getQuery()->withSavePointIfNeeded(fn () => $this->create(array_merge($attributes, $values), $joining, $touch));
} catch (UniqueConstraintViolationException $e) {

}

try {
return tap($this->related->where($attributes)->first() ?? throw $e, function ($instance) use ($joining, $touch) {
$this->getQuery()->withSavepointIfNeeded(fn () => $this->attach($instance, $joining, $touch));
});
} catch (UniqueConstraintViolationException $e) {
return (clone $this)->useWritePdo()->where($attributes)->first() ?? throw $e;
}
}










public function updateOrCreate(array $attributes, array $values = [], array $joining = [], $touch = true)
{
return tap($this->firstOrCreate($attributes, $values, $joining, $touch), function ($instance) use ($values) {
if (! $instance->wasRecentlyCreated) {
$instance->fill($values);

$instance->save(['touch' => false]);
}
});
}








public function find($id, $columns = ['*'])
{
if (! $id instanceof Model && (is_array($id) || $id instanceof Arrayable)) {
return $this->findMany($id, $columns);
}

return $this->where(
$this->getRelated()->getQualifiedKeyName(), '=', $this->parseId($id)
)->first($columns);
}








public function findMany($ids, $columns = ['*'])
{
$ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

if (empty($ids)) {
return $this->getRelated()->newCollection();
}

return $this->whereKey(
$this->parseIds($ids)
)->get($columns);
}










public function findOrFail($id, $columns = ['*'])
{
$result = $this->find($id, $columns);

$id = $id instanceof Arrayable ? $id->toArray() : $id;

if (is_array($id)) {
if (count($result) === count(array_unique($id))) {
return $result;
}
} elseif (! is_null($result)) {
return $result;
}

throw (new ModelNotFoundException)->setModel(get_class($this->related), $id);
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

$result = $this->find($id, $columns);

$id = $id instanceof Arrayable ? $id->toArray() : $id;

if (is_array($id)) {
if (count($result) === count(array_unique($id))) {
return $result;
}
} elseif (! is_null($result)) {
return $result;
}

return $callback();
}










public function firstWhere($column, $operator = null, $value = null, $boolean = 'and')
{
return $this->where($column, $operator, $value, $boolean)->first();
}







public function first($columns = ['*'])
{
$results = $this->take(1)->get($columns);

return count($results) > 0 ? $results->first() : null;
}









public function firstOrFail($columns = ['*'])
{
if (! is_null($model = $this->first($columns))) {
return $model;
}

throw (new ModelNotFoundException)->setModel(get_class($this->related));
}

/**
@template






*/
public function firstOr($columns = ['*'], ?Closure $callback = null)
{
if ($columns instanceof Closure) {
$callback = $columns;

$columns = ['*'];
}

if (! is_null($model = $this->first($columns))) {
return $model;
}

return $callback();
}


public function getResults()
{
return ! is_null($this->parent->{$this->parentKey})
? $this->get()
: $this->related->newCollection();
}


public function get($columns = ['*'])
{



$builder = $this->query->applyScopes();

$columns = $builder->getQuery()->columns ? [] : $columns;

$models = $builder->addSelect(
$this->shouldSelect($columns)
)->getModels();

$this->hydratePivotRelation($models);




if (count($models) > 0) {
$models = $builder->eagerLoadRelations($models);
}

return $this->query->applyAfterQueryCallbacks(
$this->related->newCollection($models)
);
}







protected function shouldSelect(array $columns = ['*'])
{
if ($columns == ['*']) {
$columns = [$this->related->getTable().'.*'];
}

return array_merge($columns, $this->aliasedPivotColumns());
}








protected function aliasedPivotColumns()
{
$defaults = [$this->foreignPivotKey, $this->relatedPivotKey];

return collect(array_merge($defaults, $this->pivotColumns))->map(function ($column) {
return $this->qualifyPivotColumn($column).' as pivot_'.$column;
})->unique()->all();
}










public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
{
$this->query->addSelect($this->shouldSelect($columns));

return tap($this->query->paginate($perPage, $columns, $pageName, $page), function ($paginator) {
$this->hydratePivotRelation($paginator->items());
});
}










public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
{
$this->query->addSelect($this->shouldSelect($columns));

return tap($this->query->simplePaginate($perPage, $columns, $pageName, $page), function ($paginator) {
$this->hydratePivotRelation($paginator->items());
});
}










public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
{
$this->query->addSelect($this->shouldSelect($columns));

return tap($this->query->cursorPaginate($perPage, $columns, $cursorName, $cursor), function ($paginator) {
$this->hydratePivotRelation($paginator->items());
});
}








public function chunk($count, callable $callback)
{
return $this->prepareQueryBuilder()->chunk($count, function ($results, $page) use ($callback) {
$this->hydratePivotRelation($results->all());

return $callback($results, $page);
});
}










public function chunkById($count, callable $callback, $column = null, $alias = null)
{
return $this->orderedChunkById($count, $callback, $column, $alias);
}










public function chunkByIdDesc($count, callable $callback, $column = null, $alias = null)
{
return $this->orderedChunkById($count, $callback, $column, $alias, descending: true);
}










public function eachById(callable $callback, $count = 1000, $column = null, $alias = null)
{
return $this->chunkById($count, function ($results, $page) use ($callback, $count) {
foreach ($results as $key => $value) {
if ($callback($value, (($page - 1) * $count) + $key) === false) {
return false;
}
}
}, $column, $alias);
}











public function orderedChunkById($count, callable $callback, $column = null, $alias = null, $descending = false)
{
$column ??= $this->getRelated()->qualifyColumn(
$this->getRelatedKeyName()
);

$alias ??= $this->getRelatedKeyName();

return $this->prepareQueryBuilder()->orderedChunkById($count, function ($results, $page) use ($callback) {
$this->hydratePivotRelation($results->all());

return $callback($results, $page);
}, $column, $alias, $descending);
}








public function each(callable $callback, $count = 1000)
{
return $this->chunk($count, function ($results) use ($callback) {
foreach ($results as $key => $value) {
if ($callback($value, $key) === false) {
return false;
}
}
});
}







public function lazy($chunkSize = 1000)
{
return $this->prepareQueryBuilder()->lazy($chunkSize)->map(function ($model) {
$this->hydratePivotRelation([$model]);

return $model;
});
}









public function lazyById($chunkSize = 1000, $column = null, $alias = null)
{
$column ??= $this->getRelated()->qualifyColumn(
$this->getRelatedKeyName()
);

$alias ??= $this->getRelatedKeyName();

return $this->prepareQueryBuilder()->lazyById($chunkSize, $column, $alias)->map(function ($model) {
$this->hydratePivotRelation([$model]);

return $model;
});
}









public function lazyByIdDesc($chunkSize = 1000, $column = null, $alias = null)
{
$column ??= $this->getRelated()->qualifyColumn(
$this->getRelatedKeyName()
);

$alias ??= $this->getRelatedKeyName();

return $this->prepareQueryBuilder()->lazyByIdDesc($chunkSize, $column, $alias)->map(function ($model) {
$this->hydratePivotRelation([$model]);

return $model;
});
}






public function cursor()
{
return $this->prepareQueryBuilder()->cursor()->map(function ($model) {
$this->hydratePivotRelation([$model]);

return $model;
});
}






protected function prepareQueryBuilder()
{
return $this->query->addSelect($this->shouldSelect());
}







protected function hydratePivotRelation(array $models)
{



foreach ($models as $model) {
$model->setRelation($this->accessor, $this->newExistingPivot(
$this->migratePivotAttributes($model)
));
}
}







protected function migratePivotAttributes(Model $model)
{
$values = [];

foreach ($model->getAttributes() as $key => $value) {



if (str_starts_with($key, 'pivot_')) {
$values[substr($key, 6)] = $value;

unset($model->$key);
}
}

return $values;
}






public function touchIfTouching()
{
if ($this->touchingParent()) {
$this->getParent()->touch();
}

if ($this->getParent()->touches($this->relationName)) {
$this->touch();
}
}






protected function touchingParent()
{
return $this->getRelated()->touches($this->guessInverseRelation());
}






protected function guessInverseRelation()
{
return Str::camel(Str::pluralStudly(class_basename($this->getParent())));
}








public function touch()
{
if ($this->related->isIgnoringTouch()) {
return;
}

$columns = [
$this->related->getUpdatedAtColumn() => $this->related->freshTimestampString(),
];




if (count($ids = $this->allRelatedIds()) > 0) {
$this->getRelated()->newQueryWithoutRelationships()->whereKey($ids)->update($columns);
}
}






public function allRelatedIds()
{
return $this->newPivotQuery()->pluck($this->relatedPivotKey);
}









public function save(Model $model, array $pivotAttributes = [], $touch = true)
{
$model->save(['touch' => false]);

$this->attach($model, $pivotAttributes, $touch);

return $model;
}









public function saveQuietly(Model $model, array $pivotAttributes = [], $touch = true)
{
return Model::withoutEvents(function () use ($model, $pivotAttributes, $touch) {
return $this->save($model, $pivotAttributes, $touch);
});
}

/**
@template






*/
public function saveMany($models, array $pivotAttributes = [])
{
foreach ($models as $key => $model) {
$this->save($model, (array) ($pivotAttributes[$key] ?? []), false);
}

$this->touchIfTouching();

return $models;
}

/**
@template






*/
public function saveManyQuietly($models, array $pivotAttributes = [])
{
return Model::withoutEvents(function () use ($models, $pivotAttributes) {
return $this->saveMany($models, $pivotAttributes);
});
}









public function create(array $attributes = [], array $joining = [], $touch = true)
{
$instance = $this->related->newInstance($attributes);




$instance->save(['touch' => false]);

$this->attach($instance, $joining, $touch);

return $instance;
}








public function createMany(iterable $records, array $joinings = [])
{
$instances = [];

foreach ($records as $key => $record) {
$instances[] = $this->create($record, (array) ($joinings[$key] ?? []), false);
}

$this->touchIfTouching();

return $instances;
}


public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
{
if ($parentQuery->getQuery()->from == $query->getQuery()->from) {
return $this->getRelationExistenceQueryForSelfJoin($query, $parentQuery, $columns);
}

$this->performJoin($query);

return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
}









public function getRelationExistenceQueryForSelfJoin(Builder $query, Builder $parentQuery, $columns = ['*'])
{
$query->select($columns);

$query->from($this->related->getTable().' as '.$hash = $this->getRelationCountHash());

$this->related->setTable($hash);

$this->performJoin($query);

return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
}







public function take($value)
{
return $this->limit($value);
}







public function limit($value)
{
if ($this->parent->exists) {
$this->query->limit($value);
} else {
$column = $this->getExistenceCompareKey();

$grammar = $this->query->getQuery()->getGrammar();

if ($grammar instanceof MySqlGrammar && $grammar->useLegacyGroupLimit($this->query->getQuery())) {
$column = 'pivot_'.last(explode('.', $column));
}

$this->query->groupLimit($value, $column);
}

return $this;
}






public function getExistenceCompareKey()
{
return $this->getQualifiedForeignPivotKeyName();
}








public function withTimestamps($createdAt = null, $updatedAt = null)
{
$this->withTimestamps = true;

$this->pivotCreatedAt = $createdAt;
$this->pivotUpdatedAt = $updatedAt;

return $this->withPivot($this->createdAt(), $this->updatedAt());
}






public function createdAt()
{
return $this->pivotCreatedAt ?? $this->parent->getCreatedAtColumn() ?? Model::CREATED_AT;
}






public function updatedAt()
{
return $this->pivotUpdatedAt ?? $this->parent->getUpdatedAtColumn() ?? Model::UPDATED_AT;
}






public function getForeignPivotKeyName()
{
return $this->foreignPivotKey;
}






public function getQualifiedForeignPivotKeyName()
{
return $this->qualifyPivotColumn($this->foreignPivotKey);
}






public function getRelatedPivotKeyName()
{
return $this->relatedPivotKey;
}






public function getQualifiedRelatedPivotKeyName()
{
return $this->qualifyPivotColumn($this->relatedPivotKey);
}






public function getParentKeyName()
{
return $this->parentKey;
}






public function getQualifiedParentKeyName()
{
return $this->parent->qualifyColumn($this->parentKey);
}






public function getRelatedKeyName()
{
return $this->relatedKey;
}






public function getQualifiedRelatedKeyName()
{
return $this->related->qualifyColumn($this->relatedKey);
}






public function getTable()
{
return $this->table;
}






public function getRelationName()
{
return $this->relationName;
}






public function getPivotAccessor()
{
return $this->accessor;
}






public function getPivotColumns()
{
return $this->pivotColumns;
}







public function qualifyPivotColumn($column)
{
if ($this->query->getQuery()->getGrammar()->isExpression($column)) {
return $column;
}

return str_contains($column, '.')
? $column
: $this->table.'.'.$column;
}
}
