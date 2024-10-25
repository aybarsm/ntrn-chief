<?php

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
@template
@template
@extends

*/
class MorphToMany extends BelongsToMany
{





protected $morphType;






protected $morphClass;








protected $inverse;
















public function __construct(Builder $query, Model $parent, $name, $table, $foreignPivotKey,
$relatedPivotKey, $parentKey, $relatedKey, $relationName = null, $inverse = false)
{
$this->inverse = $inverse;
$this->morphType = $name.'_type';
$this->morphClass = $inverse ? $query->getModel()->getMorphClass() : $parent->getMorphClass();

parent::__construct(
$query, $parent, $table, $foreignPivotKey,
$relatedPivotKey, $parentKey, $relatedKey, $relationName
);
}






protected function addWhereConstraints()
{
parent::addWhereConstraints();

$this->query->where($this->qualifyPivotColumn($this->morphType), $this->morphClass);

return $this;
}


public function addEagerConstraints(array $models)
{
parent::addEagerConstraints($models);

$this->query->where($this->qualifyPivotColumn($this->morphType), $this->morphClass);
}








protected function baseAttachRecord($id, $timed)
{
return Arr::add(
parent::baseAttachRecord($id, $timed), $this->morphType, $this->morphClass
);
}


public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
{
return parent::getRelationExistenceQuery($query, $parentQuery, $columns)->where(
$this->qualifyPivotColumn($this->morphType), $this->morphClass
);
}






protected function getCurrentlyAttachedPivots()
{
return parent::getCurrentlyAttachedPivots()->map(function ($record) {
return $record instanceof MorphPivot
? $record->setMorphType($this->morphType)
->setMorphClass($this->morphClass)
: $record;
});
}






public function newPivotQuery()
{
return parent::newPivotQuery()->where($this->morphType, $this->morphClass);
}








public function newPivot(array $attributes = [], $exists = false)
{
$using = $this->using;

$attributes = array_merge([$this->morphType => $this->morphClass], $attributes);

$pivot = $using ? $using::fromRawAttributes($this->parent, $attributes, $this->table, $exists)
: MorphPivot::fromAttributes($this->parent, $attributes, $this->table, $exists);

$pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey)
->setMorphType($this->morphType)
->setMorphClass($this->morphClass);

return $pivot;
}








protected function aliasedPivotColumns()
{
$defaults = [$this->foreignPivotKey, $this->relatedPivotKey, $this->morphType];

return collect(array_merge($defaults, $this->pivotColumns))->map(function ($column) {
return $this->qualifyPivotColumn($column).' as pivot_'.$column;
})->unique()->all();
}






public function getMorphType()
{
return $this->morphType;
}






public function getQualifiedMorphTypeName()
{
return $this->qualifyPivotColumn($this->morphType);
}






public function getMorphClass()
{
return $this->morphClass;
}






public function getInverse()
{
return $this->inverse;
}
}
