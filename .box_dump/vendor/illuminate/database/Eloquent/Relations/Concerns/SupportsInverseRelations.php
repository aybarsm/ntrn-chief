<?php

namespace Illuminate\Database\Eloquent\Relations\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait SupportsInverseRelations
{





protected ?string $inverseRelationship = null;









public function inverse(?string $relation = null)
{
return $this->chaperone($relation);
}







public function chaperone(?string $relation = null)
{
$relation ??= $this->guessInverseRelation();

if (! $relation || ! $this->getModel()->isRelation($relation)) {
throw RelationNotFoundException::make($this->getModel(), $relation ?: 'null');
}

if ($this->inverseRelationship === null && $relation) {
$this->query->afterQuery(function ($result) {
return $this->inverseRelationship
? $this->applyInverseRelationToCollection($result, $this->getParent())
: $result;
});
}

$this->inverseRelationship = $relation;

return $this;
}






protected function guessInverseRelation(): ?string
{
return Arr::first(
$this->getPossibleInverseRelations(),
fn ($relation) => $relation && $this->getModel()->isRelation($relation)
);
}






protected function getPossibleInverseRelations(): array
{
return array_filter(array_unique([
Str::camel(Str::beforeLast($this->getForeignKeyName(), $this->getParent()->getKeyName())),
Str::camel(Str::beforeLast($this->getParent()->getForeignKey(), $this->getParent()->getKeyName())),
Str::camel(class_basename($this->getParent())),
'owner',
get_class($this->getParent()) === get_class($this->getModel()) ? 'parent' : null,
]));
}








protected function applyInverseRelationToCollection($models, ?Model $parent = null)
{
$parent ??= $this->getParent();

foreach ($models as $model) {
$model instanceof Model && $this->applyInverseRelationToModel($model, $parent);
}

return $models;
}








protected function applyInverseRelationToModel(Model $model, ?Model $parent = null)
{
if ($inverse = $this->getInverseRelationship()) {
$parent ??= $this->getParent();

$model->setRelation($inverse, $parent);
}

return $model;
}






public function getInverseRelationship()
{
return $this->inverseRelationship;
}








public function withoutInverse()
{
return $this->withoutChaperone();
}






public function withoutChaperone()
{
$this->inverseRelationship = null;

return $this;
}
}
