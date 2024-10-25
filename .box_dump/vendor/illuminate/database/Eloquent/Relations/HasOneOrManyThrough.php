<?php

namespace Illuminate\Database\Eloquent\Relations;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\UniqueConstraintViolationException;

/**
@template
@template
@template
@template
@extends

*/
abstract class HasOneOrManyThrough extends Relation
{
use InteractsWithDictionary;






protected $throughParent;






protected $farParent;






protected $firstKey;






protected $secondKey;






protected $localKey;






protected $secondLocalKey;













public function __construct(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
{
$this->localKey = $localKey;
$this->firstKey = $firstKey;
$this->secondKey = $secondKey;
$this->farParent = $farParent;
$this->throughParent = $throughParent;
$this->secondLocalKey = $secondLocalKey;

parent::__construct($query, $throughParent);
}






public function addConstraints()
{
$localValue = $this->farParent[$this->localKey];

$this->performJoin();

if (static::$constraints) {
$this->query->where($this->getQualifiedFirstKeyName(), '=', $localValue);
}
}







protected function performJoin(?Builder $query = null)
{
$query = $query ?: $this->query;

$farKey = $this->getQualifiedFarKeyName();

$query->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $farKey);

if ($this->throughParentSoftDeletes()) {
$query->withGlobalScope('SoftDeletableHasManyThrough', function ($query) {
$query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
});
}
}






public function getQualifiedParentKeyName()
{
return $this->parent->qualifyColumn($this->secondLocalKey);
}






public function throughParentSoftDeletes()
{
return in_array(SoftDeletes::class, class_uses_recursive($this->throughParent));
}






public function withTrashedParents()
{
$this->query->withoutGlobalScope('SoftDeletableHasManyThrough');

return $this;
}


public function addEagerConstraints(array $models)
{
$whereIn = $this->whereInMethod($this->farParent, $this->localKey);

$this->whereInEager(
$whereIn,
$this->getQualifiedFirstKeyName(),
$this->getKeys($models, $this->localKey)
);
}







protected function buildDictionary(Collection $results)
{
$dictionary = [];




foreach ($results as $result) {
$dictionary[$result->laravel_through_key][] = $result;
}

return $dictionary;
}








public function firstOrNew(array $attributes = [], array $values = [])
{
if (! is_null($instance = $this->where($attributes)->first())) {
return $instance;
}

return $this->related->newInstance(array_merge($attributes, $values));
}








public function firstOrCreate(array $attributes = [], array $values = [])
{
if (! is_null($instance = (clone $this)->where($attributes)->first())) {
return $instance;
}

return $this->createOrFirst(array_merge($attributes, $values));
}








public function createOrFirst(array $attributes = [], array $values = [])
{
try {
return $this->getQuery()->withSavepointIfNeeded(fn () => $this->create(array_merge($attributes, $values)));
} catch (UniqueConstraintViolationException $exception) {
return $this->where($attributes)->first() ?? throw $exception;
}
}








public function updateOrCreate(array $attributes, array $values = [])
{
return tap($this->firstOrCreate($attributes, $values), function ($instance) use ($values) {
if (! $instance->wasRecentlyCreated) {
$instance->fill($values)->save();
}
});
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








public function find($id, $columns = ['*'])
{
if (is_array($id) || $id instanceof Arrayable) {
return $this->findMany($id, $columns);
}

return $this->where(
$this->getRelated()->getQualifiedKeyName(), '=', $id
)->first($columns);
}








public function findMany($ids, $columns = ['*'])
{
$ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

if (empty($ids)) {
return $this->getRelated()->newCollection();
}

return $this->whereIn(
$this->getRelated()->getQualifiedKeyName(), $ids
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


public function get($columns = ['*'])
{
$builder = $this->prepareQueryBuilder($columns);

$models = $builder->getModels();




if (count($models) > 0) {
$models = $builder->eagerLoadRelations($models);
}

return $this->query->applyAfterQueryCallbacks(
$this->related->newCollection($models)
);
}










public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
{
$this->query->addSelect($this->shouldSelect($columns));

return $this->query->paginate($perPage, $columns, $pageName, $page);
}










public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
{
$this->query->addSelect($this->shouldSelect($columns));

return $this->query->simplePaginate($perPage, $columns, $pageName, $page);
}










public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
{
$this->query->addSelect($this->shouldSelect($columns));

return $this->query->cursorPaginate($perPage, $columns, $cursorName, $cursor);
}







protected function shouldSelect(array $columns = ['*'])
{
if ($columns == ['*']) {
$columns = [$this->related->getTable().'.*'];
}

return array_merge($columns, [$this->getQualifiedFirstKeyName().' as laravel_through_key']);
}








public function chunk($count, callable $callback)
{
return $this->prepareQueryBuilder()->chunk($count, $callback);
}










public function chunkById($count, callable $callback, $column = null, $alias = null)
{
$column ??= $this->getRelated()->getQualifiedKeyName();

$alias ??= $this->getRelated()->getKeyName();

return $this->prepareQueryBuilder()->chunkById($count, $callback, $column, $alias);
}










public function chunkByIdDesc($count, callable $callback, $column = null, $alias = null)
{
$column ??= $this->getRelated()->getQualifiedKeyName();

$alias ??= $this->getRelated()->getKeyName();

return $this->prepareQueryBuilder()->chunkByIdDesc($count, $callback, $column, $alias);
}










public function eachById(callable $callback, $count = 1000, $column = null, $alias = null)
{
$column = $column ?? $this->getRelated()->getQualifiedKeyName();

$alias = $alias ?? $this->getRelated()->getKeyName();

return $this->prepareQueryBuilder()->eachById($callback, $count, $column, $alias);
}






public function cursor()
{
return $this->prepareQueryBuilder()->cursor();
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
return $this->prepareQueryBuilder()->lazy($chunkSize);
}









public function lazyById($chunkSize = 1000, $column = null, $alias = null)
{
$column ??= $this->getRelated()->getQualifiedKeyName();

$alias ??= $this->getRelated()->getKeyName();

return $this->prepareQueryBuilder()->lazyById($chunkSize, $column, $alias);
}









public function lazyByIdDesc($chunkSize = 1000, $column = null, $alias = null)
{
$column ??= $this->getRelated()->getQualifiedKeyName();

$alias ??= $this->getRelated()->getKeyName();

return $this->prepareQueryBuilder()->lazyByIdDesc($chunkSize, $column, $alias);
}







protected function prepareQueryBuilder($columns = ['*'])
{
$builder = $this->query->applyScopes();

return $builder->addSelect(
$this->shouldSelect($builder->getQuery()->columns ? [] : $columns)
);
}


public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
{
if ($parentQuery->getQuery()->from === $query->getQuery()->from) {
return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
}

if ($parentQuery->getQuery()->from === $this->throughParent->getTable()) {
return $this->getRelationExistenceQueryForThroughSelfRelation($query, $parentQuery, $columns);
}

$this->performJoin($query);

return $query->select($columns)->whereColumn(
$this->getQualifiedLocalKeyName(), '=', $this->getQualifiedFirstKeyName()
);
}









public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
{
$query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());

$query->join($this->throughParent->getTable(), $this->getQualifiedParentKeyName(), '=', $hash.'.'.$this->secondKey);

if ($this->throughParentSoftDeletes()) {
$query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
}

$query->getModel()->setTable($hash);

return $query->select($columns)->whereColumn(
$parentQuery->getQuery()->from.'.'.$this->localKey, '=', $this->getQualifiedFirstKeyName()
);
}









public function getRelationExistenceQueryForThroughSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
{
$table = $this->throughParent->getTable().' as '.$hash = $this->getRelationCountHash();

$query->join($table, $hash.'.'.$this->secondLocalKey, '=', $this->getQualifiedFarKeyName());

if ($this->throughParentSoftDeletes()) {
$query->whereNull($hash.'.'.$this->throughParent->getDeletedAtColumn());
}

return $query->select($columns)->whereColumn(
$parentQuery->getQuery()->from.'.'.$this->localKey, '=', $hash.'.'.$this->firstKey
);
}







public function take($value)
{
return $this->limit($value);
}







public function limit($value)
{
if ($this->farParent->exists) {
$this->query->limit($value);
} else {
$column = $this->getQualifiedFirstKeyName();

$grammar = $this->query->getQuery()->getGrammar();

if ($grammar instanceof MySqlGrammar && $grammar->useLegacyGroupLimit($this->query->getQuery())) {
$column = 'laravel_through_key';
}

$this->query->groupLimit($value, $column);
}

return $this;
}






public function getQualifiedFarKeyName()
{
return $this->getQualifiedForeignKeyName();
}






public function getFirstKeyName()
{
return $this->firstKey;
}






public function getQualifiedFirstKeyName()
{
return $this->throughParent->qualifyColumn($this->firstKey);
}






public function getForeignKeyName()
{
return $this->secondKey;
}






public function getQualifiedForeignKeyName()
{
return $this->related->qualifyColumn($this->secondKey);
}






public function getLocalKeyName()
{
return $this->localKey;
}






public function getQualifiedLocalKeyName()
{
return $this->farParent->qualifyColumn($this->localKey);
}






public function getSecondLocalKeyName()
{
return $this->secondLocalKey;
}
}
