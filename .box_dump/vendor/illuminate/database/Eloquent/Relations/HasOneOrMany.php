<?php

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsInverseRelations;
use Illuminate\Database\UniqueConstraintViolationException;

/**
@template
@template
@template
@extends

*/
abstract class HasOneOrMany extends Relation
{
use InteractsWithDictionary, SupportsInverseRelations;






protected $foreignKey;






protected $localKey;










public function __construct(Builder $query, Model $parent, $foreignKey, $localKey)
{
$this->localKey = $localKey;
$this->foreignKey = $foreignKey;

parent::__construct($query, $parent);
}







public function make(array $attributes = [])
{
return tap($this->related->newInstance($attributes), function ($instance) {
$this->setForeignAttributesForCreate($instance);
$this->applyInverseRelationToModel($instance);
});
}







public function makeMany($records)
{
$instances = $this->related->newCollection();

foreach ($records as $record) {
$instances->push($this->make($record));
}

return $instances;
}






public function addConstraints()
{
if (static::$constraints) {
$query = $this->getRelationQuery();

$query->where($this->foreignKey, '=', $this->getParentKey());

$query->whereNotNull($this->foreignKey);
}
}


public function addEagerConstraints(array $models)
{
$whereIn = $this->whereInMethod($this->parent, $this->localKey);

$this->whereInEager(
$whereIn,
$this->foreignKey,
$this->getKeys($models, $this->localKey),
$this->getRelationQuery()
);
}









public function matchOne(array $models, Collection $results, $relation)
{
return $this->matchOneOrMany($models, $results, $relation, 'one');
}









public function matchMany(array $models, Collection $results, $relation)
{
return $this->matchOneOrMany($models, $results, $relation, 'many');
}










protected function matchOneOrMany(array $models, Collection $results, $relation, $type)
{
$dictionary = $this->buildDictionary($results);




foreach ($models as $model) {
if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
$related = $this->getRelationValue($dictionary, $key, $type);
$model->setRelation($relation, $related);


$type === 'one'
? $this->applyInverseRelationToModel($related, $model)
: $this->applyInverseRelationToCollection($related, $model);
}
}

return $models;
}









protected function getRelationValue(array $dictionary, $key, $type)
{
$value = $dictionary[$key];

return $type === 'one' ? reset($value) : $this->related->newCollection($value);
}







protected function buildDictionary(Collection $results)
{
$foreign = $this->getForeignKeyName();

return $results->mapToDictionary(function ($result) use ($foreign) {
return [$this->getDictionaryKey($result->{$foreign}) => $result];
})->all();
}








public function findOrNew($id, $columns = ['*'])
{
if (is_null($instance = $this->find($id, $columns))) {
$instance = $this->related->newInstance();

$this->setForeignAttributesForCreate($instance);
}

return $instance;
}








public function firstOrNew(array $attributes = [], array $values = [])
{
if (is_null($instance = $this->where($attributes)->first())) {
$instance = $this->related->newInstance(array_merge($attributes, $values));

$this->setForeignAttributesForCreate($instance);
}

return $instance;
}








public function firstOrCreate(array $attributes = [], array $values = [])
{
if (is_null($instance = (clone $this)->where($attributes)->first())) {
$instance = $this->createOrFirst($attributes, $values);
}

return $instance;
}








public function createOrFirst(array $attributes = [], array $values = [])
{
try {
return $this->getQuery()->withSavepointIfNeeded(fn () => $this->create(array_merge($attributes, $values)));
} catch (UniqueConstraintViolationException $e) {
return $this->useWritePdo()->where($attributes)->first() ?? throw $e;
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









public function upsert(array $values, $uniqueBy, $update = null)
{
if (! empty($values) && ! is_array(reset($values))) {
$values = [$values];
}

foreach ($values as $key => $value) {
$values[$key][$this->getForeignKeyName()] = $this->getParentKey();
}

return $this->getQuery()->upsert($values, $uniqueBy, $update);
}







public function save(Model $model)
{
$this->setForeignAttributesForCreate($model);

return $model->save() ? $model : false;
}







public function saveQuietly(Model $model)
{
return Model::withoutEvents(function () use ($model) {
return $this->save($model);
});
}







public function saveMany($models)
{
foreach ($models as $model) {
$this->save($model);
}

return $models;
}







public function saveManyQuietly($models)
{
return Model::withoutEvents(function () use ($models) {
return $this->saveMany($models);
});
}







public function create(array $attributes = [])
{
return tap($this->related->newInstance($attributes), function ($instance) {
$this->setForeignAttributesForCreate($instance);

$instance->save();

$this->applyInverseRelationToModel($instance);
});
}







public function createQuietly(array $attributes = [])
{
return Model::withoutEvents(fn () => $this->create($attributes));
}







public function forceCreate(array $attributes = [])
{
$attributes[$this->getForeignKeyName()] = $this->getParentKey();

return $this->applyInverseRelationToModel($this->related->forceCreate($attributes));
}







public function forceCreateQuietly(array $attributes = [])
{
return Model::withoutEvents(fn () => $this->forceCreate($attributes));
}







public function createMany(iterable $records)
{
$instances = $this->related->newCollection();

foreach ($records as $record) {
$instances->push($this->create($record));
}

return $instances;
}







public function createManyQuietly(iterable $records)
{
return Model::withoutEvents(fn () => $this->createMany($records));
}







protected function setForeignAttributesForCreate(Model $model)
{
$model->setAttribute($this->getForeignKeyName(), $this->getParentKey());

$this->applyInverseRelationToModel($model);
}


public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
{
if ($query->getQuery()->from == $parentQuery->getQuery()->from) {
return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
}

return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
}









public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
{
$query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());

$query->getModel()->setTable($hash);

return $query->select($columns)->whereColumn(
$this->getQualifiedParentKeyName(), '=', $hash.'.'.$this->getForeignKeyName()
);
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
$this->query->groupLimit($value, $this->getExistenceCompareKey());
}

return $this;
}






public function getExistenceCompareKey()
{
return $this->getQualifiedForeignKeyName();
}






public function getParentKey()
{
return $this->parent->getAttribute($this->localKey);
}






public function getQualifiedParentKeyName()
{
return $this->parent->qualifyColumn($this->localKey);
}






public function getForeignKeyName()
{
$segments = explode('.', $this->getQualifiedForeignKeyName());

return end($segments);
}






public function getQualifiedForeignKeyName()
{
return $this->foreignKey;
}






public function getLocalKeyName()
{
return $this->localKey;
}
}
