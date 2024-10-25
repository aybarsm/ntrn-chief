<?php

namespace Illuminate\Database\Eloquent\Concerns;

use Closure;
use Illuminate\Database\ClassMorphViolationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\PendingHasThroughRelationship;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasRelationships
{





protected $relations = [];






protected $touches = [];






public static $manyMethods = [
'belongsToMany', 'morphToMany', 'morphedByMany',
];






protected static $relationResolvers = [];








public function relationResolver($class, $key)
{
if ($resolver = static::$relationResolvers[$class][$key] ?? null) {
return $resolver;
}

if ($parent = get_parent_class($class)) {
return $this->relationResolver($parent, $key);
}

return null;
}








public static function resolveRelationUsing($name, Closure $callback)
{
static::$relationResolvers = array_replace_recursive(
static::$relationResolvers,
[static::class => [$name => $callback]]
);
}

/**
@template







*/
public function hasOne($related, $foreignKey = null, $localKey = null)
{
$instance = $this->newRelatedInstance($related);

$foreignKey = $foreignKey ?: $this->getForeignKey();

$localKey = $localKey ?: $this->getKeyName();

return $this->newHasOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
}

/**
@template
@template








*/
protected function newHasOne(Builder $query, Model $parent, $foreignKey, $localKey)
{
return new HasOne($query, $parent, $foreignKey, $localKey);
}

/**
@template
@template










*/
public function hasOneThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null, $secondLocalKey = null)
{
$through = $this->newRelatedThroughInstance($through);

$firstKey = $firstKey ?: $this->getForeignKey();

$secondKey = $secondKey ?: $through->getForeignKey();

return $this->newHasOneThrough(
$this->newRelatedInstance($related)->newQuery(), $this, $through,
$firstKey, $secondKey, $localKey ?: $this->getKeyName(),
$secondLocalKey ?: $through->getKeyName()
);
}

/**
@template
@template
@template











*/
protected function newHasOneThrough(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
{
return new HasOneThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
}

/**
@template









*/
public function morphOne($related, $name, $type = null, $id = null, $localKey = null)
{
$instance = $this->newRelatedInstance($related);

[$type, $id] = $this->getMorphs($name, $type, $id);

$table = $instance->getTable();

$localKey = $localKey ?: $this->getKeyName();

return $this->newMorphOne($instance->newQuery(), $this, $table.'.'.$type, $table.'.'.$id, $localKey);
}

/**
@template
@template









*/
protected function newMorphOne(Builder $query, Model $parent, $type, $id, $localKey)
{
return new MorphOne($query, $parent, $type, $id, $localKey);
}

/**
@template








*/
public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
{



if (is_null($relation)) {
$relation = $this->guessBelongsToRelation();
}

$instance = $this->newRelatedInstance($related);




if (is_null($foreignKey)) {
$foreignKey = Str::snake($relation).'_'.$instance->getKeyName();
}




$ownerKey = $ownerKey ?: $instance->getKeyName();

return $this->newBelongsTo(
$instance->newQuery(), $this, $foreignKey, $ownerKey, $relation
);
}

/**
@template
@template









*/
protected function newBelongsTo(Builder $query, Model $child, $foreignKey, $ownerKey, $relation)
{
return new BelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
}










public function morphTo($name = null, $type = null, $id = null, $ownerKey = null)
{



$name = $name ?: $this->guessBelongsToRelation();

[$type, $id] = $this->getMorphs(
Str::snake($name), $type, $id
);




return is_null($class = $this->getAttributeFromArray($type)) || $class === ''
? $this->morphEagerTo($name, $type, $id, $ownerKey)
: $this->morphInstanceTo($class, $name, $type, $id, $ownerKey);
}










protected function morphEagerTo($name, $type, $id, $ownerKey)
{
return $this->newMorphTo(
$this->newQuery()->setEagerLoads([]), $this, $id, $ownerKey, $type, $name
);
}











protected function morphInstanceTo($target, $name, $type, $id, $ownerKey)
{
$instance = $this->newRelatedInstance(
static::getActualClassNameForMorph($target)
);

return $this->newMorphTo(
$instance->newQuery(), $this, $id, $ownerKey ?? $instance->getKeyName(), $type, $name
);
}

/**
@template
@template










*/
protected function newMorphTo(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation)
{
return new MorphTo($query, $parent, $foreignKey, $ownerKey, $type, $relation);
}







public static function getActualClassNameForMorph($class)
{
return Arr::get(Relation::morphMap() ?: [], $class, $class);
}






protected function guessBelongsToRelation()
{
[$one, $two, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

return $caller['function'];
}

/**
@template









*/
public function through($relationship)
{
if (is_string($relationship)) {
$relationship = $this->{$relationship}();
}

return new PendingHasThroughRelationship($this, $relationship);
}

/**
@template







*/
public function hasMany($related, $foreignKey = null, $localKey = null)
{
$instance = $this->newRelatedInstance($related);

$foreignKey = $foreignKey ?: $this->getForeignKey();

$localKey = $localKey ?: $this->getKeyName();

return $this->newHasMany(
$instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey
);
}

/**
@template
@template








*/
protected function newHasMany(Builder $query, Model $parent, $foreignKey, $localKey)
{
return new HasMany($query, $parent, $foreignKey, $localKey);
}

/**
@template
@template










*/
public function hasManyThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null, $secondLocalKey = null)
{
$through = $this->newRelatedThroughInstance($through);

$firstKey = $firstKey ?: $this->getForeignKey();

$secondKey = $secondKey ?: $through->getForeignKey();

return $this->newHasManyThrough(
$this->newRelatedInstance($related)->newQuery(),
$this,
$through,
$firstKey,
$secondKey,
$localKey ?: $this->getKeyName(),
$secondLocalKey ?: $through->getKeyName()
);
}

/**
@template
@template
@template











*/
protected function newHasManyThrough(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
{
return new HasManyThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
}

/**
@template









*/
public function morphMany($related, $name, $type = null, $id = null, $localKey = null)
{
$instance = $this->newRelatedInstance($related);




[$type, $id] = $this->getMorphs($name, $type, $id);

$table = $instance->getTable();

$localKey = $localKey ?: $this->getKeyName();

return $this->newMorphMany($instance->newQuery(), $this, $table.'.'.$type, $table.'.'.$id, $localKey);
}

/**
@template
@template









*/
protected function newMorphMany(Builder $query, Model $parent, $type, $id, $localKey)
{
return new MorphMany($query, $parent, $type, $id, $localKey);
}

/**
@template











*/
public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null,
$parentKey = null, $relatedKey = null, $relation = null)
{



if (is_null($relation)) {
$relation = $this->guessBelongsToManyRelation();
}




$instance = $this->newRelatedInstance($related);

$foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

$relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();




if (is_null($table)) {
$table = $this->joiningTable($related, $instance);
}

return $this->newBelongsToMany(
$instance->newQuery(), $this, $table, $foreignPivotKey,
$relatedPivotKey, $parentKey ?: $this->getKeyName(),
$relatedKey ?: $instance->getKeyName(), $relation
);
}

/**
@template
@template












*/
protected function newBelongsToMany(Builder $query, Model $parent, $table, $foreignPivotKey, $relatedPivotKey,
$parentKey, $relatedKey, $relationName = null)
{
return new BelongsToMany($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
}

/**
@template













*/
public function morphToMany($related, $name, $table = null, $foreignPivotKey = null,
$relatedPivotKey = null, $parentKey = null,
$relatedKey = null, $relation = null, $inverse = false)
{
$relation = $relation ?: $this->guessBelongsToManyRelation();




$instance = $this->newRelatedInstance($related);

$foreignPivotKey = $foreignPivotKey ?: $name.'_id';

$relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();




if (! $table) {
$words = preg_split('/(_)/u', $name, -1, PREG_SPLIT_DELIM_CAPTURE);

$lastWord = array_pop($words);

$table = implode('', $words).Str::plural($lastWord);
}

return $this->newMorphToMany(
$instance->newQuery(), $this, $name, $table,
$foreignPivotKey, $relatedPivotKey, $parentKey ?: $this->getKeyName(),
$relatedKey ?: $instance->getKeyName(), $relation, $inverse
);
}

/**
@template
@template














*/
protected function newMorphToMany(Builder $query, Model $parent, $name, $table, $foreignPivotKey,
$relatedPivotKey, $parentKey, $relatedKey,
$relationName = null, $inverse = false)
{
return new MorphToMany($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
$relationName, $inverse);
}

/**
@template












*/
public function morphedByMany($related, $name, $table = null, $foreignPivotKey = null,
$relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null)
{
$foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();




$relatedPivotKey = $relatedPivotKey ?: $name.'_id';

return $this->morphToMany(
$related, $name, $table, $foreignPivotKey,
$relatedPivotKey, $parentKey, $relatedKey, $relation, true
);
}






protected function guessBelongsToManyRelation()
{
$caller = Arr::first(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), function ($trace) {
return ! in_array(
$trace['function'],
array_merge(static::$manyMethods, ['guessBelongsToManyRelation'])
);
});

return ! is_null($caller) ? $caller['function'] : null;
}








public function joiningTable($related, $instance = null)
{



$segments = [
$instance ? $instance->joiningTableSegment()
: Str::snake(class_basename($related)),
$this->joiningTableSegment(),
];




sort($segments);

return strtolower(implode('_', $segments));
}






public function joiningTableSegment()
{
return Str::snake(class_basename($this));
}







public function touches($relation)
{
return in_array($relation, $this->getTouchedRelations());
}






public function touchOwners()
{
$this->withoutRecursion(function () {
foreach ($this->getTouchedRelations() as $relation) {
$this->$relation()->touch();

if ($this->$relation instanceof self) {
$this->$relation->fireModelEvent('saved', false);

$this->$relation->touchOwners();
} elseif ($this->$relation instanceof Collection) {
$this->$relation->each->touchOwners();
}
}
});
}









protected function getMorphs($name, $type, $id)
{
return [$type ?: $name.'_type', $id ?: $name.'_id'];
}






public function getMorphClass()
{
$morphMap = Relation::morphMap();

if (! empty($morphMap) && in_array(static::class, $morphMap)) {
return array_search(static::class, $morphMap, true);
}

if (static::class === Pivot::class) {
return static::class;
}

if (Relation::requiresMorphMap()) {
throw new ClassMorphViolationException($this);
}

return static::class;
}







protected function newRelatedInstance($class)
{
return tap(new $class, function ($instance) {
if (! $instance->getConnectionName()) {
$instance->setConnection($this->connection);
}
});
}







protected function newRelatedThroughInstance($class)
{
return new $class;
}






public function getRelations()
{
return $this->relations;
}







public function getRelation($relation)
{
return $this->relations[$relation];
}







public function relationLoaded($key)
{
return array_key_exists($key, $this->relations);
}








public function setRelation($relation, $value)
{
$this->relations[$relation] = $value;

return $this;
}







public function unsetRelation($relation)
{
unset($this->relations[$relation]);

return $this;
}







public function setRelations(array $relations)
{
$this->relations = $relations;

return $this;
}






public function withoutRelations()
{
$model = clone $this;

return $model->unsetRelations();
}






public function unsetRelations()
{
$this->relations = [];

return $this;
}






public function getTouchedRelations()
{
return $this->touches;
}







public function setTouchedRelations(array $touches)
{
$this->touches = $touches;

return $this;
}
}
