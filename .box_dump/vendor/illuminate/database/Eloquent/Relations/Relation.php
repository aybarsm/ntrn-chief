<?php

namespace Illuminate\Database\Eloquent\Relations;

use Closure;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;

/**
@template
@template
@template
@mixin

*/
abstract class Relation implements BuilderContract
{
use ForwardsCalls, Macroable {
Macroable::__call as macroCall;
}






protected $query;






protected $parent;






protected $related;






protected $eagerKeysWereEmpty = false;






protected static $constraints = true;






public static $morphMap = [];






protected static $requireMorphMap = false;






protected static $selfJoinCount = 0;








public function __construct(Builder $query, Model $parent)
{
$this->query = $query;
$this->parent = $parent;
$this->related = $query->getModel();

$this->addConstraints();
}







public static function noConstraints(Closure $callback)
{
$previous = static::$constraints;

static::$constraints = false;




try {
return $callback();
} finally {
static::$constraints = $previous;
}
}






abstract public function addConstraints();







abstract public function addEagerConstraints(array $models);








abstract public function initRelation(array $models, $relation);









abstract public function match(array $models, Collection $results, $relation);






abstract public function getResults();






public function getEager()
{
return $this->eagerKeysWereEmpty
? $this->query->getModel()->newCollection()
: $this->get();
}










public function sole($columns = ['*'])
{
$result = $this->take(2)->get($columns);

$count = $result->count();

if ($count === 0) {
throw (new ModelNotFoundException)->setModel(get_class($this->related));
}

if ($count > 1) {
throw new MultipleRecordsFoundException($count);
}

return $result->first();
}







public function get($columns = ['*'])
{
return $this->query->get($columns);
}






public function touch()
{
$model = $this->getRelated();

if (! $model::isIgnoringTouch()) {
$this->rawUpdate([
$model->getUpdatedAtColumn() => $model->freshTimestampString(),
]);
}
}







public function rawUpdate(array $attributes = [])
{
return $this->query->withoutGlobalScopes()->update($attributes);
}








public function getRelationExistenceCountQuery(Builder $query, Builder $parentQuery)
{
return $this->getRelationExistenceQuery(
$query, $parentQuery, new Expression('count(*)')
)->setBindings([], 'select');
}











public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
{
return $query->select($columns)->whereColumn(
$this->getQualifiedParentKeyName(), '=', $this->getExistenceCompareKey()
);
}







public function getRelationCountHash($incrementJoinCount = true)
{
return 'laravel_reserved_'.($incrementJoinCount ? static::$selfJoinCount++ : static::$selfJoinCount);
}








protected function getKeys(array $models, $key = null)
{
return collect($models)->map(function ($value) use ($key) {
return $key ? $value->getAttribute($key) : $value->getKey();
})->values()->unique(null, true)->sort()->all();
}






protected function getRelationQuery()
{
return $this->query;
}






public function getQuery()
{
return $this->query;
}






public function getBaseQuery()
{
return $this->query->getQuery();
}






public function toBase()
{
return $this->query->toBase();
}






public function getParent()
{
return $this->parent;
}






public function getQualifiedParentKeyName()
{
return $this->parent->getQualifiedKeyName();
}






public function getRelated()
{
return $this->related;
}






public function createdAt()
{
return $this->parent->getCreatedAtColumn();
}






public function updatedAt()
{
return $this->parent->getUpdatedAtColumn();
}






public function relatedUpdatedAt()
{
return $this->related->getUpdatedAtColumn();
}










protected function whereInEager(string $whereIn, string $key, array $modelKeys, ?Builder $query = null)
{
($query ?? $this->query)->{$whereIn}($key, $modelKeys);

if ($modelKeys === []) {
$this->eagerKeysWereEmpty = true;
}
}








protected function whereInMethod(Model $model, $key)
{
return $model->getKeyName() === last(explode('.', $key))
&& in_array($model->getKeyType(), ['int', 'integer'])
? 'whereIntegerInRaw'
: 'whereIn';
}







public static function requireMorphMap($requireMorphMap = true)
{
static::$requireMorphMap = $requireMorphMap;
}






public static function requiresMorphMap()
{
return static::$requireMorphMap;
}








public static function enforceMorphMap(array $map, $merge = true)
{
static::requireMorphMap();

return static::morphMap($map, $merge);
}








public static function morphMap(?array $map = null, $merge = true)
{
$map = static::buildMorphMapFromModels($map);

if (is_array($map)) {
static::$morphMap = $merge && static::$morphMap
? $map + static::$morphMap : $map;
}

return static::$morphMap;
}







protected static function buildMorphMapFromModels(?array $models = null)
{
if (is_null($models) || ! array_is_list($models)) {
return $models;
}

return array_combine(array_map(function ($model) {
return (new $model)->getTable();
}, $models), $models);
}







public static function getMorphedModel($alias)
{
return static::$morphMap[$alias] ?? null;
}







public static function getMorphAlias(string $className)
{
return array_search($className, static::$morphMap, strict: true) ?: $className;
}








public function __call($method, $parameters)
{
if (static::hasMacro($method)) {
return $this->macroCall($method, $parameters);
}

return $this->forwardDecoratedCallTo($this->query, $method, $parameters);
}






public function __clone()
{
$this->query = clone $this->query;
}
}
