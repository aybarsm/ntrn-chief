<?php

namespace Illuminate\Database\Eloquent\Relations;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;

/**
@template
@template
@extends

*/
class MorphTo extends BelongsTo
{
use InteractsWithDictionary;






protected $morphType;






protected $models;






protected $dictionary = [];






protected $macroBuffer = [];






protected $morphableEagerLoads = [];






protected $morphableEagerLoadCounts = [];






protected $morphableConstraints = [];












public function __construct(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation)
{
$this->morphType = $type;

parent::__construct($query, $parent, $foreignKey, $ownerKey, $relation);
}


public function addEagerConstraints(array $models)
{
$this->buildDictionary($this->models = Collection::make($models));
}







protected function buildDictionary(Collection $models)
{
foreach ($models as $model) {
if ($model->{$this->morphType}) {
$morphTypeKey = $this->getDictionaryKey($model->{$this->morphType});
$foreignKeyKey = $this->getDictionaryKey($model->{$this->foreignKey});

$this->dictionary[$morphTypeKey][$foreignKeyKey][] = $model;
}
}
}








public function getEager()
{
foreach (array_keys($this->dictionary) as $type) {
$this->matchToMorphParents($type, $this->getResultsByType($type));
}

return $this->models;
}







protected function getResultsByType($type)
{
$instance = $this->createModelByType($type);

$ownerKey = $this->ownerKey ?? $instance->getKeyName();

$query = $this->replayMacros($instance->newQuery())
->mergeConstraintsFrom($this->getQuery())
->with(array_merge(
$this->getQuery()->getEagerLoads(),
(array) ($this->morphableEagerLoads[get_class($instance)] ?? [])
))
->withCount(
(array) ($this->morphableEagerLoadCounts[get_class($instance)] ?? [])
);

if ($callback = ($this->morphableConstraints[get_class($instance)] ?? null)) {
$callback($query);
}

$whereIn = $this->whereInMethod($instance, $ownerKey);

return $query->{$whereIn}(
$instance->getTable().'.'.$ownerKey, $this->gatherKeysByType($type, $instance->getKeyType())
)->get();
}








protected function gatherKeysByType($type, $keyType)
{
return $keyType !== 'string'
? array_keys($this->dictionary[$type])
: array_map(function ($modelId) {
return (string) $modelId;
}, array_filter(array_keys($this->dictionary[$type])));
}







public function createModelByType($type)
{
$class = Model::getActualClassNameForMorph($type);

return tap(new $class, function ($instance) {
if (! $instance->getConnectionName()) {
$instance->setConnection($this->getConnection()->getName());
}
});
}


public function match(array $models, Collection $results, $relation)
{
return $models;
}








protected function matchToMorphParents($type, Collection $results)
{
foreach ($results as $result) {
$ownerKey = ! is_null($this->ownerKey) ? $this->getDictionaryKey($result->{$this->ownerKey}) : $result->getKey();

if (isset($this->dictionary[$type][$ownerKey])) {
foreach ($this->dictionary[$type][$ownerKey] as $model) {
$model->setRelation($this->relationName, $result);
}
}
}
}







public function associate($model)
{
if ($model instanceof Model) {
$foreignKey = $this->ownerKey && $model->{$this->ownerKey}
? $this->ownerKey
: $model->getKeyName();
}

$this->parent->setAttribute(
$this->foreignKey, $model instanceof Model ? $model->{$foreignKey} : null
);

$this->parent->setAttribute(
$this->morphType, $model instanceof Model ? $model->getMorphClass() : null
);

return $this->parent->setRelation($this->relationName, $model);
}






public function dissociate()
{
$this->parent->setAttribute($this->foreignKey, null);

$this->parent->setAttribute($this->morphType, null);

return $this->parent->setRelation($this->relationName, null);
}






public function touch()
{
if (! is_null($this->getParentKey())) {
parent::touch();
}
}


protected function newRelatedInstanceFor(Model $parent)
{
return $parent->{$this->getRelationName()}()->getRelated()->newInstance();
}






public function getMorphType()
{
return $this->morphType;
}






public function getDictionary()
{
return $this->dictionary;
}







public function morphWith(array $with)
{
$this->morphableEagerLoads = array_merge(
$this->morphableEagerLoads, $with
);

return $this;
}







public function morphWithCount(array $withCount)
{
$this->morphableEagerLoadCounts = array_merge(
$this->morphableEagerLoadCounts, $withCount
);

return $this;
}







public function constrain(array $callbacks)
{
$this->morphableConstraints = array_merge(
$this->morphableConstraints, $callbacks
);

return $this;
}






public function withTrashed()
{
$callback = fn ($query) => $query->hasMacro('withTrashed') ? $query->withTrashed() : $query;

$this->macroBuffer[] = [
'method' => 'when',
'parameters' => [true, $callback],
];

return $this->when(true, $callback);
}






public function withoutTrashed()
{
$callback = fn ($query) => $query->hasMacro('withoutTrashed') ? $query->withoutTrashed() : $query;

$this->macroBuffer[] = [
'method' => 'when',
'parameters' => [true, $callback],
];

return $this->when(true, $callback);
}






public function onlyTrashed()
{
$callback = fn ($query) => $query->hasMacro('onlyTrashed') ? $query->onlyTrashed() : $query;

$this->macroBuffer[] = [
'method' => 'when',
'parameters' => [true, $callback],
];

return $this->when(true, $callback);
}







protected function replayMacros(Builder $query)
{
foreach ($this->macroBuffer as $macro) {
$query->{$macro['method']}(...$macro['parameters']);
}

return $query;
}








public function __call($method, $parameters)
{
try {
$result = parent::__call($method, $parameters);

if (in_array($method, ['select', 'selectRaw', 'selectSub', 'addSelect', 'withoutGlobalScopes'])) {
$this->macroBuffer[] = compact('method', 'parameters');
}

return $result;
}




catch (BadMethodCallException) {
$this->macroBuffer[] = compact('method', 'parameters');

return $this;
}
}
}
