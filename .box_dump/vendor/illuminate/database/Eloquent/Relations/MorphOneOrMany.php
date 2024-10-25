<?php

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
@template
@template
@template
@extends

*/
abstract class MorphOneOrMany extends HasOneOrMany
{





protected $morphType;






protected $morphClass;











public function __construct(Builder $query, Model $parent, $type, $id, $localKey)
{
$this->morphType = $type;

$this->morphClass = $parent->getMorphClass();

parent::__construct($query, $parent, $id, $localKey);
}






public function addConstraints()
{
if (static::$constraints) {
$this->getRelationQuery()->where($this->morphType, $this->morphClass);

parent::addConstraints();
}
}


public function addEagerConstraints(array $models)
{
parent::addEagerConstraints($models);

$this->getRelationQuery()->where($this->morphType, $this->morphClass);
}







public function forceCreate(array $attributes = [])
{
$attributes[$this->getForeignKeyName()] = $this->getParentKey();
$attributes[$this->getMorphType()] = $this->morphClass;

return $this->applyInverseRelationToModel($this->related->forceCreate($attributes));
}







protected function setForeignAttributesForCreate(Model $model)
{
$model->{$this->getForeignKeyName()} = $this->getParentKey();

$model->{$this->getMorphType()} = $this->morphClass;

$this->applyInverseRelationToModel($model);
}









public function upsert(array $values, $uniqueBy, $update = null)
{
if (! empty($values) && ! is_array(reset($values))) {
$values = [$values];
}

foreach ($values as $key => $value) {
$values[$key][$this->getMorphType()] = $this->getMorphClass();
}

return parent::upsert($values, $uniqueBy, $update);
}


public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
{
return parent::getRelationExistenceQuery($query, $parentQuery, $columns)->where(
$query->qualifyColumn($this->getMorphType()), $this->morphClass
);
}






public function getQualifiedMorphType()
{
return $this->morphType;
}






public function getMorphType()
{
return last(explode('.', $this->morphType));
}






public function getMorphClass()
{
return $this->morphClass;
}






protected function getPossibleInverseRelations(): array
{
return array_unique([
Str::beforeLast($this->getMorphType(), '_type'),
...parent::getPossibleInverseRelations(),
]);
}
}
