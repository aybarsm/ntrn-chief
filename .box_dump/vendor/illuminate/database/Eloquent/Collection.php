<?php

namespace Illuminate\Database\Eloquent;

use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use LogicException;

/**
@template
@template
@extends

*/
class Collection extends BaseCollection implements QueueableCollection
{
use InteractsWithDictionary;

/**
@template






*/
public function find($key, $default = null)
{
if ($key instanceof Model) {
$key = $key->getKey();
}

if ($key instanceof Arrayable) {
$key = $key->toArray();
}

if (is_array($key)) {
if ($this->isEmpty()) {
return new static;
}

return $this->whereIn($this->first()->getKeyName(), $key);
}

return Arr::first($this->items, fn ($model) => $model->getKey() == $key, $default);
}









public function findOrFail($key)
{
$result = $this->find($key);

if (is_array($key) && count($result) === count(array_unique($key))) {
return $result;
} elseif (! is_array($key) && ! is_null($result)) {
return $result;
}

$exception = new ModelNotFoundException;

if (! $model = head($this->items)) {
throw $exception;
}

$ids = is_array($key) ? array_diff($key, $result->modelKeys()) : $key;

$exception->setModel(get_class($model), $ids);

throw $exception;
}







public function load($relations)
{
if ($this->isNotEmpty()) {
if (is_string($relations)) {
$relations = func_get_args();
}

$query = $this->first()->newQueryWithoutRelationships()->with($relations);

$this->items = $query->eagerLoadRelations($this->items);
}

return $this;
}









public function loadAggregate($relations, $column, $function = null)
{
if ($this->isEmpty()) {
return $this;
}

$models = $this->first()->newModelQuery()
->whereKey($this->modelKeys())
->select($this->first()->getKeyName())
->withAggregate($relations, $column, $function)
->get()
->keyBy($this->first()->getKeyName());

$attributes = Arr::except(
array_keys($models->first()->getAttributes()),
$models->first()->getKeyName()
);

$this->each(function ($model) use ($models, $attributes) {
$extraAttributes = Arr::only($models->get($model->getKey())->getAttributes(), $attributes);

$model->forceFill($extraAttributes)
->syncOriginalAttributes($attributes)
->mergeCasts($models->get($model->getKey())->getCasts());
});

return $this;
}







public function loadCount($relations)
{
return $this->loadAggregate($relations, '*', 'count');
}








public function loadMax($relations, $column)
{
return $this->loadAggregate($relations, $column, 'max');
}








public function loadMin($relations, $column)
{
return $this->loadAggregate($relations, $column, 'min');
}








public function loadSum($relations, $column)
{
return $this->loadAggregate($relations, $column, 'sum');
}








public function loadAvg($relations, $column)
{
return $this->loadAggregate($relations, $column, 'avg');
}







public function loadExists($relations)
{
return $this->loadAggregate($relations, '*', 'exists');
}







public function loadMissing($relations)
{
if (is_string($relations)) {
$relations = func_get_args();
}

foreach ($relations as $key => $value) {
if (is_numeric($key)) {
$key = $value;
}

$segments = explode('.', explode(':', $key)[0]);

if (str_contains($key, ':')) {
$segments[count($segments) - 1] .= ':'.explode(':', $key)[1];
}

$path = [];

foreach ($segments as $segment) {
$path[] = [$segment => $segment];
}

if (is_callable($value)) {
$path[count($segments) - 1][end($segments)] = $value;
}

$this->loadMissingRelation($this, $path);
}

return $this;
}








protected function loadMissingRelation(self $models, array $path)
{
$relation = array_shift($path);

$name = explode(':', key($relation))[0];

if (is_string(reset($relation))) {
$relation = reset($relation);
}

$models->filter(fn ($model) => ! is_null($model) && ! $model->relationLoaded($name))->load($relation);

if (empty($path)) {
return;
}

$models = $models->pluck($name)->whereNotNull();

if ($models->first() instanceof BaseCollection) {
$models = $models->collapse();
}

$this->loadMissingRelation(new static($models), $path);
}








public function loadMorph($relation, $relations)
{
$this->pluck($relation)
->filter()
->groupBy(fn ($model) => get_class($model))
->each(fn ($models, $className) => static::make($models)->load($relations[$className] ?? []));

return $this;
}








public function loadMorphCount($relation, $relations)
{
$this->pluck($relation)
->filter()
->groupBy(fn ($model) => get_class($model))
->each(fn ($models, $className) => static::make($models)->loadCount($relations[$className] ?? []));

return $this;
}









public function contains($key, $operator = null, $value = null)
{
if (func_num_args() > 1 || $this->useAsCallable($key)) {
return parent::contains(...func_get_args());
}

if ($key instanceof Model) {
return parent::contains(fn ($model) => $model->is($key));
}

return parent::contains(fn ($model) => $model->getKey() == $key);
}






public function modelKeys()
{
return array_map(fn ($model) => $model->getKey(), $this->items);
}







public function merge($items)
{
$dictionary = $this->getDictionary();

foreach ($items as $item) {
$dictionary[$this->getDictionaryKey($item->getKey())] = $item;
}

return new static(array_values($dictionary));
}

/**
@template





*/
public function map(callable $callback)
{
$result = parent::map($callback);

return $result->contains(fn ($item) => ! $item instanceof Model) ? $result->toBase() : $result;
}

/**
@template
@template







*/
public function mapWithKeys(callable $callback)
{
$result = parent::mapWithKeys($callback);

return $result->contains(fn ($item) => ! $item instanceof Model) ? $result->toBase() : $result;
}







public function fresh($with = [])
{
if ($this->isEmpty()) {
return new static;
}

$model = $this->first();

$freshModels = $model->newQueryWithoutScopes()
->with(is_string($with) ? func_get_args() : $with)
->whereIn($model->getKeyName(), $this->modelKeys())
->get()
->getDictionary();

return $this->filter(fn ($model) => $model->exists && isset($freshModels[$model->getKey()]))
->map(fn ($model) => $freshModels[$model->getKey()]);
}







public function diff($items)
{
$diff = new static;

$dictionary = $this->getDictionary($items);

foreach ($this->items as $item) {
if (! isset($dictionary[$this->getDictionaryKey($item->getKey())])) {
$diff->add($item);
}
}

return $diff;
}







public function intersect($items)
{
$intersect = new static;

if (empty($items)) {
return $intersect;
}

$dictionary = $this->getDictionary($items);

foreach ($this->items as $item) {
if (isset($dictionary[$this->getDictionaryKey($item->getKey())])) {
$intersect->add($item);
}
}

return $intersect;
}








public function unique($key = null, $strict = false)
{
if (! is_null($key)) {
return parent::unique($key, $strict);
}

return new static(array_values($this->getDictionary()));
}







public function only($keys)
{
if (is_null($keys)) {
return new static($this->items);
}

$dictionary = Arr::only($this->getDictionary(), array_map($this->getDictionaryKey(...), (array) $keys));

return new static(array_values($dictionary));
}







public function except($keys)
{
if (is_null($keys)) {
return new static($this->items);
}

$dictionary = Arr::except($this->getDictionary(), array_map($this->getDictionaryKey(...), (array) $keys));

return new static(array_values($dictionary));
}







public function makeHidden($attributes)
{
return $this->each->makeHidden($attributes);
}







public function makeVisible($attributes)
{
return $this->each->makeVisible($attributes);
}







public function setVisible($visible)
{
return $this->each->setVisible($visible);
}







public function setHidden($hidden)
{
return $this->each->setHidden($hidden);
}







public function append($attributes)
{
return $this->each->append($attributes);
}







public function getDictionary($items = null)
{
$items = is_null($items) ? $this->items : $items;

$dictionary = [];

foreach ($items as $value) {
$dictionary[$this->getDictionaryKey($value->getKey())] = $value;
}

return $dictionary;
}











public function countBy($countBy = null)
{
return $this->toBase()->countBy($countBy);
}






public function collapse()
{
return $this->toBase()->collapse();
}







public function flatten($depth = INF)
{
return $this->toBase()->flatten($depth);
}






public function flip()
{
return $this->toBase()->flip();
}






public function keys()
{
return $this->toBase()->keys();
}

/**
@template






*/
public function pad($size, $value)
{
return $this->toBase()->pad($size, $value);
}








public function pluck($value, $key = null)
{
return $this->toBase()->pluck($value, $key);
}

/**
@template





*/
public function zip($items)
{
return $this->toBase()->zip(...func_get_args());
}







protected function duplicateComparator($strict)
{
return fn ($a, $b) => $a->is($b);
}








public function getQueueableClass()
{
if ($this->isEmpty()) {
return;
}

$class = $this->getQueueableModelClass($this->first());

$this->each(function ($model) use ($class) {
if ($this->getQueueableModelClass($model) !== $class) {
throw new LogicException('Queueing collections with multiple model types is not supported.');
}
});

return $class;
}







protected function getQueueableModelClass($model)
{
return method_exists($model, 'getQueueableClassName')
? $model->getQueueableClassName()
: get_class($model);
}






public function getQueueableIds()
{
if ($this->isEmpty()) {
return [];
}

return $this->first() instanceof QueueableEntity
? $this->map->getQueueableId()->all()
: $this->modelKeys();
}






public function getQueueableRelations()
{
if ($this->isEmpty()) {
return [];
}

$relations = $this->map->getQueueableRelations()->all();

if (count($relations) === 0 || $relations === [[]]) {
return [];
} elseif (count($relations) === 1) {
return reset($relations);
} else {
return array_intersect(...array_values($relations));
}
}








public function getQueueableConnection()
{
if ($this->isEmpty()) {
return;
}

$connection = $this->first()->getConnectionName();

$this->each(function ($model) use ($connection) {
if ($model->getConnectionName() !== $connection) {
throw new LogicException('Queueing collections with multiple model connections is not supported.');
}
});

return $connection;
}








public function toQuery()
{
$model = $this->first();

if (! $model) {
throw new LogicException('Unable to create query for empty collection.');
}

$class = get_class($model);

if ($this->filter(fn ($model) => ! $model instanceof $class)->isNotEmpty()) {
throw new LogicException('Unable to create query for collection with mixed types.');
}

return $model->newModelQuery()->whereKey($this->modelKeys());
}
}
