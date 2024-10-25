<?php

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;

/**
@template
@template
@extends

*/
class HasMany extends HasOneOrMany
{





public function one()
{
return HasOne::noConstraints(fn () => tap(
new HasOne(
$this->getQuery(),
$this->parent,
$this->foreignKey,
$this->localKey
),
function ($hasOne) {
if ($inverse = $this->getInverseRelationship()) {
$hasOne->inverse($inverse);
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
}
