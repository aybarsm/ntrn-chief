<?php

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Contracts\Database\Eloquent\SupportsPartialRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\CanBeOneOfMany;
use Illuminate\Database\Eloquent\Relations\Concerns\ComparesRelatedModels;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
use Illuminate\Database\Query\JoinClause;

/**
@template
@template
@extends

*/
class HasOne extends HasOneOrMany implements SupportsPartialRelations
{
use ComparesRelatedModels, CanBeOneOfMany, SupportsDefaultModels;


public function getResults()
{
if (is_null($this->getParentKey())) {
return $this->getDefaultFor($this->parent);
}

return $this->query->first() ?: $this->getDefaultFor($this->parent);
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
return $this->matchOne($models, $results, $relation);
}


public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
{
if ($this->isOneOfMany()) {
$this->mergeOneOfManyJoinsTo($query);
}

return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
}









public function addOneOfManySubQueryConstraints(Builder $query, $column = null, $aggregate = null)
{
$query->addSelect($this->foreignKey);
}






public function getOneOfManySubQuerySelectColumns()
{
return $this->foreignKey;
}







public function addOneOfManyJoinSubQueryConstraints(JoinClause $join)
{
$join->on($this->qualifySubSelectColumn($this->foreignKey), '=', $this->qualifyRelatedColumn($this->foreignKey));
}







public function newRelatedInstanceFor(Model $parent)
{
return tap($this->related->newInstance(), function ($instance) use ($parent) {
$instance->setAttribute($this->getForeignKeyName(), $parent->{$this->localKey});
$this->applyInverseRelationToModel($instance, $parent);
});
}







protected function getRelatedKeyFrom(Model $model)
{
return $model->getAttribute($this->getForeignKeyName());
}
}
