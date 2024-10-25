<?php

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\ComparesRelatedModels;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;

use function Illuminate\Support\enum_value;

/**
@template
@template
@extends

*/
class BelongsTo extends Relation
{
use ComparesRelatedModels,
InteractsWithDictionary,
SupportsDefaultModels;






protected $child;






protected $foreignKey;






protected $ownerKey;






protected $relationName;











public function __construct(Builder $query, Model $child, $foreignKey, $ownerKey, $relationName)
{
$this->ownerKey = $ownerKey;
$this->relationName = $relationName;
$this->foreignKey = $foreignKey;




$this->child = $child;

parent::__construct($query, $child);
}


public function getResults()
{
if (is_null($this->getForeignKeyFrom($this->child))) {
return $this->getDefaultFor($this->parent);
}

return $this->query->first() ?: $this->getDefaultFor($this->parent);
}






public function addConstraints()
{
if (static::$constraints) {



$table = $this->related->getTable();

$this->query->where($table.'.'.$this->ownerKey, '=', $this->getForeignKeyFrom($this->child));
}
}


public function addEagerConstraints(array $models)
{



$key = $this->related->getTable().'.'.$this->ownerKey;

$whereIn = $this->whereInMethod($this->related, $this->ownerKey);

$this->whereInEager($whereIn, $key, $this->getEagerModelKeys($models));
}







protected function getEagerModelKeys(array $models)
{
$keys = [];




foreach ($models as $model) {
if (! is_null($value = $this->getForeignKeyFrom($model))) {
$keys[] = $value;
}
}

sort($keys);

return array_values(array_unique($keys));
}


public function initRelation(array $models, $relation)
{
foreach ($models as $model) {
$model->setRelation($relation, $this->getDefaultFor($model));
}

return $models;
}


public function match(array $models, Collection $results, $relation)
{



$dictionary = [];

foreach ($results as $result) {
$attribute = $this->getDictionaryKey($this->getRelatedKeyFrom($result));

$dictionary[$attribute] = $result;
}




foreach ($models as $model) {
$attribute = $this->getDictionaryKey($this->getForeignKeyFrom($model));

if (isset($dictionary[$attribute])) {
$model->setRelation($relation, $dictionary[$attribute]);
}
}

return $models;
}







public function associate($model)
{
$ownerKey = $model instanceof Model ? $model->getAttribute($this->ownerKey) : $model;

$this->child->setAttribute($this->foreignKey, $ownerKey);

if ($model instanceof Model) {
$this->child->setRelation($this->relationName, $model);
} else {
$this->child->unsetRelation($this->relationName);
}

return $this->child;
}






public function dissociate()
{
$this->child->setAttribute($this->foreignKey, null);

return $this->child->setRelation($this->relationName, null);
}






public function disassociate()
{
return $this->dissociate();
}






public function touch()
{
if (! is_null($this->getParentKey())) {
parent::touch();
}
}


public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
{
if ($parentQuery->getQuery()->from == $query->getQuery()->from) {
return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
}

return $query->select($columns)->whereColumn(
$this->getQualifiedForeignKeyName(), '=', $query->qualifyColumn($this->ownerKey)
);
}









public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
{
$query->select($columns)->from(
$query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash()
);

$query->getModel()->setTable($hash);

return $query->whereColumn(
$hash.'.'.$this->ownerKey, '=', $this->getQualifiedForeignKeyName()
);
}






protected function relationHasIncrementingId()
{
return $this->related->getIncrementing() &&
in_array($this->related->getKeyType(), ['int', 'integer']);
}







protected function newRelatedInstanceFor(Model $parent)
{
return $this->related->newInstance();
}






public function getChild()
{
return $this->child;
}






public function getForeignKeyName()
{
return $this->foreignKey;
}






public function getQualifiedForeignKeyName()
{
return $this->child->qualifyColumn($this->foreignKey);
}






public function getParentKey()
{
return $this->getForeignKeyFrom($this->child);
}






public function getOwnerKeyName()
{
return $this->ownerKey;
}






public function getQualifiedOwnerKeyName()
{
return $this->related->qualifyColumn($this->ownerKey);
}







protected function getRelatedKeyFrom(Model $model)
{
return $model->{$this->ownerKey};
}







protected function getForeignKeyFrom(Model $model)
{
$foreignKey = $model->{$this->foreignKey};

return enum_value($foreignKey);
}






public function getRelationName()
{
return $this->relationName;
}
}
