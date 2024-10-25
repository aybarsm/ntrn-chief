<?php

namespace Illuminate\Database\Eloquent;

use ArrayAccess;
use Illuminate\Contracts\Broadcasting\HasBroadcastChannel;
use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\CanBeEscapedWhenCastToString;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use JsonException;
use JsonSerializable;
use LogicException;
use Stringable;

abstract class Model implements Arrayable, ArrayAccess, CanBeEscapedWhenCastToString, HasBroadcastChannel, Jsonable, JsonSerializable, QueueableEntity, Stringable, UrlRoutable
{
use Concerns\HasAttributes,
Concerns\HasEvents,
Concerns\HasGlobalScopes,
Concerns\HasRelationships,
Concerns\HasTimestamps,
Concerns\HasUniqueIds,
Concerns\HidesAttributes,
Concerns\GuardsAttributes,
Concerns\PreventsCircularRecursion,
ForwardsCalls;
/**
@use */
use HasCollection;






protected $connection;






protected $table;






protected $primaryKey = 'id';






protected $keyType = 'int';






public $incrementing = true;






protected $with = [];






protected $withCount = [];






public $preventsLazyLoading = false;






protected $perPage = 15;






public $exists = false;






public $wasRecentlyCreated = false;






protected $escapeWhenCastingToString = false;






protected static $resolver;






protected static $dispatcher;






protected static $booted = [];






protected static $traitInitializers = [];






protected static $globalScopes = [];






protected static $ignoreOnTouch = [];






protected static $modelsShouldPreventLazyLoading = false;






protected static $lazyLoadingViolationCallback;






protected static $modelsShouldPreventSilentlyDiscardingAttributes = false;






protected static $discardedAttributeViolationCallback;






protected static $modelsShouldPreventAccessingMissingAttributes = false;






protected static $missingAttributeViolationCallback;






protected static $isBroadcasting = true;






protected static string $builder = Builder::class;






protected static string $collectionClass = Collection::class;






const CREATED_AT = 'created_at';






const UPDATED_AT = 'updated_at';







public function __construct(array $attributes = [])
{
$this->bootIfNotBooted();

$this->initializeTraits();

$this->syncOriginal();

$this->fill($attributes);
}






protected function bootIfNotBooted()
{
if (! isset(static::$booted[static::class])) {
static::$booted[static::class] = true;

$this->fireModelEvent('booting', false);

static::booting();
static::boot();
static::booted();

$this->fireModelEvent('booted', false);
}
}






protected static function booting()
{

}






protected static function boot()
{
static::bootTraits();
}






protected static function bootTraits()
{
$class = static::class;

$booted = [];

static::$traitInitializers[$class] = [];

foreach (class_uses_recursive($class) as $trait) {
$method = 'boot'.class_basename($trait);

if (method_exists($class, $method) && ! in_array($method, $booted)) {
forward_static_call([$class, $method]);

$booted[] = $method;
}

if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
static::$traitInitializers[$class][] = $method;

static::$traitInitializers[$class] = array_unique(
static::$traitInitializers[$class]
);
}
}
}






protected function initializeTraits()
{
foreach (static::$traitInitializers[static::class] as $method) {
$this->{$method}();
}
}






protected static function booted()
{

}






public static function clearBootedModels()
{
static::$booted = [];

static::$globalScopes = [];
}







public static function withoutTouching(callable $callback)
{
static::withoutTouchingOn([static::class], $callback);
}








public static function withoutTouchingOn(array $models, callable $callback)
{
static::$ignoreOnTouch = array_values(array_merge(static::$ignoreOnTouch, $models));

try {
$callback();
} finally {
static::$ignoreOnTouch = array_values(array_diff(static::$ignoreOnTouch, $models));
}
}







public static function isIgnoringTouch($class = null)
{
$class = $class ?: static::class;

if (! get_class_vars($class)['timestamps'] || ! $class::UPDATED_AT) {
return true;
}

foreach (static::$ignoreOnTouch as $ignoredClass) {
if ($class === $ignoredClass || is_subclass_of($class, $ignoredClass)) {
return true;
}
}

return false;
}







public static function shouldBeStrict(bool $shouldBeStrict = true)
{
static::preventLazyLoading($shouldBeStrict);
static::preventSilentlyDiscardingAttributes($shouldBeStrict);
static::preventAccessingMissingAttributes($shouldBeStrict);
}







public static function preventLazyLoading($value = true)
{
static::$modelsShouldPreventLazyLoading = $value;
}







public static function handleLazyLoadingViolationUsing(?callable $callback)
{
static::$lazyLoadingViolationCallback = $callback;
}







public static function preventSilentlyDiscardingAttributes($value = true)
{
static::$modelsShouldPreventSilentlyDiscardingAttributes = $value;
}







public static function handleDiscardedAttributeViolationUsing(?callable $callback)
{
static::$discardedAttributeViolationCallback = $callback;
}







public static function preventAccessingMissingAttributes($value = true)
{
static::$modelsShouldPreventAccessingMissingAttributes = $value;
}







public static function handleMissingAttributeViolationUsing(?callable $callback)
{
static::$missingAttributeViolationCallback = $callback;
}







public static function withoutBroadcasting(callable $callback)
{
$isBroadcasting = static::$isBroadcasting;

static::$isBroadcasting = false;

try {
return $callback();
} finally {
static::$isBroadcasting = $isBroadcasting;
}
}









public function fill(array $attributes)
{
$totallyGuarded = $this->totallyGuarded();

$fillable = $this->fillableFromArray($attributes);

foreach ($fillable as $key => $value) {



if ($this->isFillable($key)) {
$this->setAttribute($key, $value);
} elseif ($totallyGuarded || static::preventsSilentlyDiscardingAttributes()) {
if (isset(static::$discardedAttributeViolationCallback)) {
call_user_func(static::$discardedAttributeViolationCallback, $this, [$key]);
} else {
throw new MassAssignmentException(sprintf(
'Add [%s] to fillable property to allow mass assignment on [%s].',
$key, get_class($this)
));
}
}
}

if (count($attributes) !== count($fillable) &&
static::preventsSilentlyDiscardingAttributes()) {
$keys = array_diff(array_keys($attributes), array_keys($fillable));

if (isset(static::$discardedAttributeViolationCallback)) {
call_user_func(static::$discardedAttributeViolationCallback, $this, $keys);
} else {
throw new MassAssignmentException(sprintf(
'Add fillable property [%s] to allow mass assignment on [%s].',
implode(', ', $keys),
get_class($this)
));
}
}

return $this;
}







public function forceFill(array $attributes)
{
return static::unguarded(fn () => $this->fill($attributes));
}







public function qualifyColumn($column)
{
if (str_contains($column, '.')) {
return $column;
}

return $this->getTable().'.'.$column;
}







public function qualifyColumns($columns)
{
return collect($columns)->map(function ($column) {
return $this->qualifyColumn($column);
})->all();
}








public function newInstance($attributes = [], $exists = false)
{



$model = new static;

$model->exists = $exists;

$model->setConnection(
$this->getConnectionName()
);

$model->setTable($this->getTable());

$model->mergeCasts($this->casts);

$model->fill((array) $attributes);

return $model;
}








public function newFromBuilder($attributes = [], $connection = null)
{
$model = $this->newInstance([], true);

$model->setRawAttributes((array) $attributes, true);

$model->setConnection($connection ?: $this->getConnectionName());

$model->fireModelEvent('retrieved', false);

return $model;
}







public static function on($connection = null)
{



$instance = new static;

$instance->setConnection($connection);

return $instance->newQuery();
}






public static function onWriteConnection()
{
return static::query()->useWritePdo();
}







public static function all($columns = ['*'])
{
return static::query()->get(
is_array($columns) ? $columns : func_get_args()
);
}







public static function with($relations)
{
return static::query()->with(
is_string($relations) ? func_get_args() : $relations
);
}







public function load($relations)
{
$query = $this->newQueryWithoutRelationships()->with(
is_string($relations) ? func_get_args() : $relations
);

$query->eagerLoadRelations([$this]);

return $this;
}








public function loadMorph($relation, $relations)
{
if (! $this->{$relation}) {
return $this;
}

$className = get_class($this->{$relation});

$this->{$relation}->load($relations[$className] ?? []);

return $this;
}







public function loadMissing($relations)
{
$relations = is_string($relations) ? func_get_args() : $relations;

$this->newCollection([$this])->loadMissing($relations);

return $this;
}









public function loadAggregate($relations, $column, $function = null)
{
$this->newCollection([$this])->loadAggregate($relations, $column, $function);

return $this;
}







public function loadCount($relations)
{
$relations = is_string($relations) ? func_get_args() : $relations;

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










public function loadMorphAggregate($relation, $relations, $column, $function = null)
{
if (! $this->{$relation}) {
return $this;
}

$className = get_class($this->{$relation});

$this->{$relation}->loadAggregate($relations[$className] ?? [], $column, $function);

return $this;
}








public function loadMorphCount($relation, $relations)
{
return $this->loadMorphAggregate($relation, $relations, '*', 'count');
}









public function loadMorphMax($relation, $relations, $column)
{
return $this->loadMorphAggregate($relation, $relations, $column, 'max');
}









public function loadMorphMin($relation, $relations, $column)
{
return $this->loadMorphAggregate($relation, $relations, $column, 'min');
}









public function loadMorphSum($relation, $relations, $column)
{
return $this->loadMorphAggregate($relation, $relations, $column, 'sum');
}









public function loadMorphAvg($relation, $relations, $column)
{
return $this->loadMorphAggregate($relation, $relations, $column, 'avg');
}









protected function increment($column, $amount = 1, array $extra = [])
{
return $this->incrementOrDecrement($column, $amount, $extra, 'increment');
}









protected function decrement($column, $amount = 1, array $extra = [])
{
return $this->incrementOrDecrement($column, $amount, $extra, 'decrement');
}










protected function incrementOrDecrement($column, $amount, $extra, $method)
{
if (! $this->exists) {
return $this->newQueryWithoutRelationships()->{$method}($column, $amount, $extra);
}

$this->{$column} = $this->isClassDeviable($column)
? $this->deviateClassCastableAttribute($method, $column, $amount)
: $this->{$column} + ($method === 'increment' ? $amount : $amount * -1);

$this->forceFill($extra);

if ($this->fireModelEvent('updating') === false) {
return false;
}

if ($this->isClassDeviable($column)) {
$amount = (clone $this)->setAttribute($column, $amount)->getAttributeFromArray($column);
}

return tap($this->setKeysForSaveQuery($this->newQueryWithoutScopes())->{$method}($column, $amount, $extra), function () use ($column) {
$this->syncChanges();

$this->fireModelEvent('updated', false);

$this->syncOriginalAttribute($column);
});
}








public function update(array $attributes = [], array $options = [])
{
if (! $this->exists) {
return false;
}

return $this->fill($attributes)->save($options);
}










public function updateOrFail(array $attributes = [], array $options = [])
{
if (! $this->exists) {
return false;
}

return $this->fill($attributes)->saveOrFail($options);
}








public function updateQuietly(array $attributes = [], array $options = [])
{
if (! $this->exists) {
return false;
}

return $this->fill($attributes)->saveQuietly($options);
}









protected function incrementQuietly($column, $amount = 1, array $extra = [])
{
return static::withoutEvents(function () use ($column, $amount, $extra) {
return $this->incrementOrDecrement($column, $amount, $extra, 'increment');
});
}









protected function decrementQuietly($column, $amount = 1, array $extra = [])
{
return static::withoutEvents(function () use ($column, $amount, $extra) {
return $this->incrementOrDecrement($column, $amount, $extra, 'decrement');
});
}






public function push()
{
return $this->withoutRecursion(function () {
if (! $this->save()) {
return false;
}




foreach ($this->relations as $models) {
$models = $models instanceof Collection
? $models->all() : [$models];

foreach (array_filter($models) as $model) {
if (! $model->push()) {
return false;
}
}
}

return true;
}, true);
}






public function pushQuietly()
{
return static::withoutEvents(fn () => $this->push());
}







public function saveQuietly(array $options = [])
{
return static::withoutEvents(fn () => $this->save($options));
}







public function save(array $options = [])
{
$this->mergeAttributesFromCachedCasts();

$query = $this->newModelQuery();




if ($this->fireModelEvent('saving') === false) {
return false;
}




if ($this->exists) {
$saved = $this->isDirty() ?
$this->performUpdate($query) : true;
}




else {
$saved = $this->performInsert($query);

if (! $this->getConnectionName() &&
$connection = $query->getConnection()) {
$this->setConnection($connection->getName());
}
}




if ($saved) {
$this->finishSave($options);
}

return $saved;
}









public function saveOrFail(array $options = [])
{
return $this->getConnection()->transaction(fn () => $this->save($options));
}







protected function finishSave(array $options)
{
$this->fireModelEvent('saved', false);

if ($this->isDirty() && ($options['touch'] ?? true)) {
$this->touchOwners();
}

$this->syncOriginal();
}







protected function performUpdate(Builder $query)
{



if ($this->fireModelEvent('updating') === false) {
return false;
}




if ($this->usesTimestamps()) {
$this->updateTimestamps();
}




$dirty = $this->getDirtyForUpdate();

if (count($dirty) > 0) {
$this->setKeysForSaveQuery($query)->update($dirty);

$this->syncChanges();

$this->fireModelEvent('updated', false);
}

return true;
}







protected function setKeysForSelectQuery($query)
{
$query->where($this->getKeyName(), '=', $this->getKeyForSelectQuery());

return $query;
}






protected function getKeyForSelectQuery()
{
return $this->original[$this->getKeyName()] ?? $this->getKey();
}







protected function setKeysForSaveQuery($query)
{
$query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());

return $query;
}






protected function getKeyForSaveQuery()
{
return $this->original[$this->getKeyName()] ?? $this->getKey();
}







protected function performInsert(Builder $query)
{
if ($this->usesUniqueIds()) {
$this->setUniqueIds();
}

if ($this->fireModelEvent('creating') === false) {
return false;
}




if ($this->usesTimestamps()) {
$this->updateTimestamps();
}




$attributes = $this->getAttributesForInsert();

if ($this->getIncrementing()) {
$this->insertAndSetId($query, $attributes);
}




else {
if (empty($attributes)) {
return true;
}

$query->insert($attributes);
}




$this->exists = true;

$this->wasRecentlyCreated = true;

$this->fireModelEvent('created', false);

return true;
}








protected function insertAndSetId(Builder $query, $attributes)
{
$id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

$this->setAttribute($keyName, $id);
}







public static function destroy($ids)
{
if ($ids instanceof EloquentCollection) {
$ids = $ids->modelKeys();
}

if ($ids instanceof BaseCollection) {
$ids = $ids->all();
}

$ids = is_array($ids) ? $ids : func_get_args();

if (count($ids) === 0) {
return 0;
}




$key = ($instance = new static)->getKeyName();

$count = 0;

foreach ($instance->whereIn($key, $ids)->get() as $model) {
if ($model->delete()) {
$count++;
}
}

return $count;
}








public function delete()
{
$this->mergeAttributesFromCachedCasts();

if (is_null($this->getKeyName())) {
throw new LogicException('No primary key defined on model.');
}




if (! $this->exists) {
return;
}

if ($this->fireModelEvent('deleting') === false) {
return false;
}




$this->touchOwners();

$this->performDeleteOnModel();




$this->fireModelEvent('deleted', false);

return true;
}






public function deleteQuietly()
{
return static::withoutEvents(fn () => $this->delete());
}








public function deleteOrFail()
{
if (! $this->exists) {
return false;
}

return $this->getConnection()->transaction(fn () => $this->delete());
}








public function forceDelete()
{
return $this->delete();
}









public static function forceDestroy($ids)
{
return static::destroy($ids);
}






protected function performDeleteOnModel()
{
$this->setKeysForSaveQuery($this->newModelQuery())->delete();

$this->exists = false;
}






public static function query()
{
return (new static)->newQuery();
}






public function newQuery()
{
return $this->registerGlobalScopes($this->newQueryWithoutScopes());
}






public function newModelQuery()
{
return $this->newEloquentBuilder(
$this->newBaseQueryBuilder()
)->setModel($this);
}






public function newQueryWithoutRelationships()
{
return $this->registerGlobalScopes($this->newModelQuery());
}







public function registerGlobalScopes($builder)
{
foreach ($this->getGlobalScopes() as $identifier => $scope) {
$builder->withGlobalScope($identifier, $scope);
}

return $builder;
}






public function newQueryWithoutScopes()
{
return $this->newModelQuery()
->with($this->with)
->withCount($this->withCount);
}







public function newQueryWithoutScope($scope)
{
return $this->newQuery()->withoutGlobalScope($scope);
}







public function newQueryForRestoration($ids)
{
return $this->newQueryWithoutScopes()->whereKey($ids);
}







public function newEloquentBuilder($query)
{
return new static::$builder($query);
}






protected function newBaseQueryBuilder()
{
return $this->getConnection()->query();
}











public function newPivot(self $parent, array $attributes, $table, $exists, $using = null)
{
return $using ? $using::fromRawAttributes($parent, $attributes, $table, $exists)
: Pivot::fromAttributes($parent, $attributes, $table, $exists);
}







public function hasNamedScope($scope)
{
return method_exists($this, 'scope'.ucfirst($scope));
}








public function callNamedScope($scope, array $parameters = [])
{
return $this->{'scope'.ucfirst($scope)}(...$parameters);
}






public function toArray()
{
return $this->withoutRecursion(
fn () => array_merge($this->attributesToArray(), $this->relationsToArray()),
fn () => $this->attributesToArray(),
);
}









public function toJson($options = 0)
{
try {
$json = json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
throw JsonEncodingException::forModel($this, $e->getMessage());
}

return $json;
}






public function jsonSerialize(): mixed
{
return $this->toArray();
}







public function fresh($with = [])
{
if (! $this->exists) {
return;
}

return $this->setKeysForSelectQuery($this->newQueryWithoutScopes())
->useWritePdo()
->with(is_string($with) ? func_get_args() : $with)
->first();
}






public function refresh()
{
if (! $this->exists) {
return $this;
}

$this->setRawAttributes(
$this->setKeysForSelectQuery($this->newQueryWithoutScopes())
->useWritePdo()
->firstOrFail()
->attributes
);

$this->load(collect($this->relations)->reject(function ($relation) {
return $relation instanceof Pivot
|| (is_object($relation) && in_array(AsPivot::class, class_uses_recursive($relation), true));
})->keys()->all());

$this->syncOriginal();

return $this;
}







public function replicate(?array $except = null)
{
$defaults = array_values(array_filter([
$this->getKeyName(),
$this->getCreatedAtColumn(),
$this->getUpdatedAtColumn(),
...$this->uniqueIds(),
'laravel_through_key',
]));

$attributes = Arr::except(
$this->getAttributes(), $except ? array_unique(array_merge($except, $defaults)) : $defaults
);

return tap(new static, function ($instance) use ($attributes) {
$instance->setRawAttributes($attributes);

$instance->setRelations($this->relations);

$instance->fireModelEvent('replicating', false);
});
}







public function replicateQuietly(?array $except = null)
{
return static::withoutEvents(fn () => $this->replicate($except));
}







public function is($model)
{
return ! is_null($model) &&
$this->getKey() === $model->getKey() &&
$this->getTable() === $model->getTable() &&
$this->getConnectionName() === $model->getConnectionName();
}







public function isNot($model)
{
return ! $this->is($model);
}






public function getConnection()
{
return static::resolveConnection($this->getConnectionName());
}






public function getConnectionName()
{
return $this->connection;
}







public function setConnection($name)
{
$this->connection = $name;

return $this;
}







public static function resolveConnection($connection = null)
{
return static::$resolver->connection($connection);
}






public static function getConnectionResolver()
{
return static::$resolver;
}







public static function setConnectionResolver(Resolver $resolver)
{
static::$resolver = $resolver;
}






public static function unsetConnectionResolver()
{
static::$resolver = null;
}






public function getTable()
{
return $this->table ?? Str::snake(Str::pluralStudly(class_basename($this)));
}







public function setTable($table)
{
$this->table = $table;

return $this;
}






public function getKeyName()
{
return $this->primaryKey;
}







public function setKeyName($key)
{
$this->primaryKey = $key;

return $this;
}






public function getQualifiedKeyName()
{
return $this->qualifyColumn($this->getKeyName());
}






public function getKeyType()
{
return $this->keyType;
}







public function setKeyType($type)
{
$this->keyType = $type;

return $this;
}






public function getIncrementing()
{
return $this->incrementing;
}







public function setIncrementing($value)
{
$this->incrementing = $value;

return $this;
}






public function getKey()
{
return $this->getAttribute($this->getKeyName());
}






public function getQueueableId()
{
return $this->getKey();
}






public function getQueueableRelations()
{
return $this->withoutRecursion(function () {
$relations = [];

foreach ($this->getRelations() as $key => $relation) {
if (! method_exists($this, $key)) {
continue;
}

$relations[] = $key;

if ($relation instanceof QueueableCollection) {
foreach ($relation->getQueueableRelations() as $collectionValue) {
$relations[] = $key.'.'.$collectionValue;
}
}

if ($relation instanceof QueueableEntity) {
foreach ($relation->getQueueableRelations() as $entityValue) {
$relations[] = $key.'.'.$entityValue;
}
}
}

return array_unique($relations);
}, []);
}






public function getQueueableConnection()
{
return $this->getConnectionName();
}






public function getRouteKey()
{
return $this->getAttribute($this->getRouteKeyName());
}






public function getRouteKeyName()
{
return $this->getKeyName();
}








public function resolveRouteBinding($value, $field = null)
{
return $this->resolveRouteBindingQuery($this, $value, $field)->first();
}








public function resolveSoftDeletableRouteBinding($value, $field = null)
{
return $this->resolveRouteBindingQuery($this, $value, $field)->withTrashed()->first();
}









public function resolveChildRouteBinding($childType, $value, $field)
{
return $this->resolveChildRouteBindingQuery($childType, $value, $field)->first();
}









public function resolveSoftDeletableChildRouteBinding($childType, $value, $field)
{
return $this->resolveChildRouteBindingQuery($childType, $value, $field)->withTrashed()->first();
}









protected function resolveChildRouteBindingQuery($childType, $value, $field)
{
$relationship = $this->{$this->childRouteBindingRelationshipName($childType)}();

$field = $field ?: $relationship->getRelated()->getRouteKeyName();

if ($relationship instanceof HasManyThrough ||
$relationship instanceof BelongsToMany) {
$field = $relationship->getRelated()->getTable().'.'.$field;
}

return $relationship instanceof Model
? $relationship->resolveRouteBindingQuery($relationship, $value, $field)
: $relationship->getRelated()->resolveRouteBindingQuery($relationship, $value, $field);
}







protected function childRouteBindingRelationshipName($childType)
{
return Str::plural(Str::camel($childType));
}









public function resolveRouteBindingQuery($query, $value, $field = null)
{
return $query->where($field ?? $this->getRouteKeyName(), $value);
}






public function getForeignKey()
{
return Str::snake(class_basename($this)).'_'.$this->getKeyName();
}






public function getPerPage()
{
return $this->perPage;
}







public function setPerPage($perPage)
{
$this->perPage = $perPage;

return $this;
}






public static function preventsLazyLoading()
{
return static::$modelsShouldPreventLazyLoading;
}






public static function preventsSilentlyDiscardingAttributes()
{
return static::$modelsShouldPreventSilentlyDiscardingAttributes;
}






public static function preventsAccessingMissingAttributes()
{
return static::$modelsShouldPreventAccessingMissingAttributes;
}






public function broadcastChannelRoute()
{
return str_replace('\\', '.', get_class($this)).'.{'.Str::camel(class_basename($this)).'}';
}






public function broadcastChannel()
{
return str_replace('\\', '.', get_class($this)).'.'.$this->getKey();
}







public function __get($key)
{
return $this->getAttribute($key);
}








public function __set($key, $value)
{
$this->setAttribute($key, $value);
}







public function offsetExists($offset): bool
{
try {
return ! is_null($this->getAttribute($offset));
} catch (MissingAttributeException) {
return false;
}
}







public function offsetGet($offset): mixed
{
return $this->getAttribute($offset);
}








public function offsetSet($offset, $value): void
{
$this->setAttribute($offset, $value);
}







public function offsetUnset($offset): void
{
unset($this->attributes[$offset], $this->relations[$offset]);
}







public function __isset($key)
{
return $this->offsetExists($key);
}







public function __unset($key)
{
$this->offsetUnset($key);
}








public function __call($method, $parameters)
{
if (in_array($method, ['increment', 'decrement', 'incrementQuietly', 'decrementQuietly'])) {
return $this->$method(...$parameters);
}

if ($resolver = $this->relationResolver(static::class, $method)) {
return $resolver($this);
}

if (Str::startsWith($method, 'through') &&
method_exists($this, $relationMethod = Str::of($method)->after('through')->lcfirst()->toString())) {
return $this->through($relationMethod);
}

return $this->forwardCallTo($this->newQuery(), $method, $parameters);
}








public static function __callStatic($method, $parameters)
{
return (new static)->$method(...$parameters);
}






public function __toString()
{
return $this->escapeWhenCastingToString
? e($this->toJson())
: $this->toJson();
}







public function escapeWhenCastingToString($escape = true)
{
$this->escapeWhenCastingToString = $escape;

return $this;
}






public function __sleep()
{
$this->mergeAttributesFromCachedCasts();

$this->classCastCache = [];
$this->attributeCastCache = [];

return array_keys(get_object_vars($this));
}






public function __wakeup()
{
$this->bootIfNotBooted();

$this->initializeTraits();
}
}
