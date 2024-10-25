<?php

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;

/**
@template
@template
@template
@extends

*/
class HasOneThrough extends HasOneOrManyThrough
{
use InteractsWithDictionary, SupportsDefaultModels;


public function getResults()
{
return $this->first() ?: $this->getDefaultFor($this->farParent);
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
$dictionary = $this->buildDictionary($results);




foreach ($models as $model) {
if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
$value = $dictionary[$key];
$model->setRelation(
$relation, reset($value)
);
}
}

return $models;
}







public function newRelatedInstanceFor(Model $parent)
{
return $this->related->newInstance();
}
}
