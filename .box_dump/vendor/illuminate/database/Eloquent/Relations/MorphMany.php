<?php

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;

/**
@template
@template
@extends

*/
class MorphMany extends MorphOneOrMany
{





public function one()
{
return MorphOne::noConstraints(fn () => tap(
new MorphOne(
$this->getQuery(),
$this->getParent(),
$this->morphType,
$this->foreignKey,
$this->localKey
),
function ($morphOne) {
if ($inverse = $this->getInverseRelationship()) {
$morphOne->inverse($inverse);
}
}
));
}


public function getResults()
{
return ! is_null($this->getParentKey())
? $this->query->get()
: $this->related->newCollection();
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
return $this->matchMany($models, $results, $relation);
}


public function forceCreate(array $attributes = [])
{
$attributes[$this->getMorphType()] = $this->morphClass;

return parent::forceCreate($attributes);
}
}
