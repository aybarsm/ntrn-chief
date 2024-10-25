<?php

namespace Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;

/**
@template
@template
@template
@extends

*/
class HasManyThrough extends HasOneOrManyThrough
{
use InteractsWithDictionary;






public function one()
{
return HasOneThrough::noConstraints(fn () => new HasOneThrough(
tap($this->getQuery(), fn (Builder $query) => $query->getQuery()->joins = []),
$this->farParent,
$this->throughParent,
$this->getFirstKeyName(),
$this->secondKey,
$this->getLocalKeyName(),
$this->getSecondLocalKeyName(),
));
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
$dictionary = $this->buildDictionary($results);




foreach ($models as $model) {
if (isset($dictionary[$key = $this->getDictionaryKey($model->getAttribute($this->localKey))])) {
$model->setRelation(
$relation, $this->related->newCollection($dictionary[$key])
);
}
}

return $models;
}


public function getResults()
{
return ! is_null($this->farParent->{$this->localKey})
? $this->get()
: $this->related->newCollection();
}
}
