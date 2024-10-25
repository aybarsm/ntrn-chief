<?php

namespace Illuminate\Database\Eloquent;

use BadMethodCallException;
use Closure;
use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Eloquent\Concerns\QueriesRelationships;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use ReflectionClass;
use ReflectionMethod;

/**
@template
@property-read
@property-read
@property-read
@mixin


*/
class Builder implements BuilderContract
{
/**
@use */
use BuildsQueries, ForwardsCalls, QueriesRelationships {
BuildsQueries::sole as baseSole;
}






protected $query;






protected $model;






protected $eagerLoad = [];






protected static $macros = [];






protected $localMacros = [];






protected $onDelete;






protected $propertyPassthru = [
'from',
];






protected $passthru = [
'aggregate',
'average',
'avg',
'count',
'dd',
'ddrawsql',
'doesntexist',
'doesntexistor',
'dump',
'dumprawsql',
'exists',
'existsor',
'explain',
'getbindings',
'getconnection',
'getgrammar',
'getrawbindings',
'implode',
'insert',
'insertgetid',
'insertorignore',
'insertusing',
'insertorignoreusing',
'max',
'min',
'raw',
'rawvalue',
'sum',
'tosql',
'torawsql',
];






protected $scopes = [];






protected $removedScopes = [];






protected $afterQueryCallbacks = [];







public function __construct(QueryBuilder $query)
{
$this->query = $query;
}







public function make(array $attributes = [])
{
return $this->newModelInstance($attributes);
}








public function withGlobalScope($identifier, $scope)
{
$this->scopes[$identifier] = $scope;

if (method_exists($scope, 'extend')) {
$scope->extend($this);
}

return $this;
}







public function withoutGlobalScope($scope)
{
if (! is_string($scope)) {
$scope = get_class($scope);
}

unset($this->scopes[$scope]);

$this->removedScopes[] = $scope;

return $this;
}







public function withoutGlobalScopes(?array $scopes = null)
{
if (! is_array($scopes)) {
$scopes = array_keys($this->scopes);
}

foreach ($scopes as $scope) {
$this->withoutGlobalScope($scope);
}

return $this;
}






public function removedScopes()
{
return $this->removedScopes;
}







public function whereKey($id)
{
if ($id instanceof Model) {
$id = $id->getKey();
}

if (is_array($id) || $id instanceof Arrayable) {
if (in_array($this->model->getKeyType(), ['int', 'integer'])) {
$this->query->whereIntegerInRaw($this->model->getQualifiedKeyName(), $id);
} else {
$this->query->whereIn($this->model->getQualifiedKeyName(), $id);
}

return $this;
}

if ($id !== null && $this->model->getKeyType() === 'string') {
$id = (string) $id;
}

return $this->where($this->model->getQualifiedKeyName(), '=', $id);
}







public function whereKeyNot($id)
{
if ($id instanceof Model) {
$id = $id->getKey();
}

if (is_array($id) || $id instanceof Arrayable) {
if (in_array($this->model->getKeyType(), ['int', 'integer'])) {
$this->query->whereIntegerNotInRaw($this->model->getQualifiedKeyName(), $id);
} else {
$this->query->whereNotIn($this->model->getQualifiedKeyName(), $id);
}

return $this;
}

if ($id !== null && $this->model->getKeyType() === 'string') {
$id = (string) $id;
}

return $this->where($this->model->getQualifiedKeyName(), '!=', $id);
}










public function where($column, $operator = null, $value = null, $boolean = 'and')
{
if ($column instanceof Closure && is_null($operator)) {
$column($query = $this->model->newQueryWithoutRelationships());

$this->query->addNestedWhereQuery($query->getQuery(), $boolean);
} else {
$this->query->where(...func_get_args());
}

return $this;
}










public function firstWhere($column, $operator = null, $value = null, $boolean = 'and')
{
return $this->where(...func_get_args())->first();
}









public function orWhere($column, $operator = null, $value = null)
{
[$value, $operator] = $this->query->prepareValueAndOperator(
$value, $operator, func_num_args() === 2
);

return $this->where($column, $operator, $value, 'or');
}










public function whereNot($column, $operator = null, $value = null, $boolean = 'and')
{
return $this->where($column, $operator, $value, $boolean.' not');
}









public function orWhereNot($column, $operator = null, $value = null)
{
return $this->whereNot($column, $operator, $value, 'or');
}







public function latest($column = null)
{
if (is_null($column)) {
$column = $this->model->getCreatedAtColumn() ?? 'created_at';
}

$this->query->latest($column);

return $this;
}







public function oldest($column = null)
{
if (is_null($column)) {
$column = $this->model->getCreatedAtColumn() ?? 'created_at';
}

$this->query->oldest($column);

return $this;
}







public function hydrate(array $items)
{
$instance = $this->newModelInstance();

return $instance->newCollection(array_map(function ($item) use ($items, $instance) {
$model = $instance->newFromBuilder($item);

if (count($items) > 1) {
$model->preventsLazyLoading = Model::preventsLazyLoading();
}

return $model;
}, $items));
}








public function fromQuery($query, $bindings = [])
{
return $this->hydrate(
$this->query->getConnection()->select($query, $bindings)
);
}








public function find($id, $columns = ['*'])
{
if (is_array($id) || $id instanceof Arrayable) {
return $this->findMany($id, $columns);
}

return $this->whereKey($id)->first($columns);
}








public function findMany($ids, $columns = ['*'])
{
$ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

if (empty($ids)) {
return $this->model->newCollection();
}

return $this->whereKey($ids)->get($columns);
}










public function findOrFail($id, $columns = ['*'])
{
$result = $this->find($id, $columns);

$id = $id instanceof Arrayable ? $id->toArray() : $id;

if (is_array($id)) {
if (count($result) !== count(array_unique($id))) {
throw (new ModelNotFoundException)->setModel(
get_class($this->model), array_diff($id, $result->modelKeys())
);
}

return $result;
}

if (is_null($result)) {
throw (new ModelNotFoundException)->setModel(
get_class($this->model), $id
);
}

return $result;
}








public function findOrNew($id, $columns = ['*'])
{
if (! is_null($model = $this->find($id, $columns))) {
return $model;
}

return $this->newModelInstance();
}

/**
@template











*/
public function findOr($id, $columns = ['*'], ?Closure $callback = null)
{
if ($columns instanceof Closure) {
$callback = $columns;

$columns = ['*'];
}

if (! is_null($model = $this->find($id, $columns))) {
return $model;
}

return $callback();
}








public function firstOrNew(array $attributes = [], array $values = [])
{
if (! is_null($instance = $this->where($attributes)->first())) {
return $instance;
}

return $this->newModelInstance(array_merge($attributes, $values));
}








public function firstOrCreate(array $attributes = [], array $values = [])
{
if (! is_null($instance = (clone $this)->where($attributes)->first())) {
return $instance;
}

return $this->createOrFirst($attributes, $values);
}








public function createOrFirst(array $attributes = [], array $values = [])
{
try {
return $this->withSavepointIfNeeded(fn () => $this->create(array_merge($attributes, $values)));
} catch (UniqueConstraintViolationException $e) {
return $this->useWritePdo()->where($attributes)->first() ?? throw $e;
}
}








public function updateOrCreate(array $attributes, array $values = [])
{
return tap($this->firstOrCreate($attributes, $values), function ($instance) use ($values) {
if (! $instance->wasRecentlyCreated) {
$instance->fill($values)->save();
}
});
}









public function firstOrFail($columns = ['*'])
{
if (! is_null($model = $this->first($columns))) {
return $model;
}

throw (new ModelNotFoundException)->setModel(get_class($this->model));
}

/**
@template






*/
public function firstOr($columns = ['*'], ?Closure $callback = null)
{
if ($columns instanceof Closure) {
$callback = $columns;

$columns = ['*'];
}

if (! is_null($model = $this->first($columns))) {
return $model;
}

return $callback();
}










public function sole($columns = ['*'])
{
try {
return $this->baseSole($columns);
} catch (RecordsNotFoundException) {
throw (new ModelNotFoundException)->setModel(get_class($this->model));
}
}







public function value($column)
{
if ($result = $this->first([$column])) {
$column = $column instanceof Expression ? $column->getValue($this->getGrammar()) : $column;

return $result->{Str::afterLast($column, '.')};
}
}










public function soleValue($column)
{
$column = $column instanceof Expression ? $column->getValue($this->getGrammar()) : $column;

return $this->sole([$column])->{Str::afterLast($column, '.')};
}









public function valueOrFail($column)
{
$column = $column instanceof Expression ? $column->getValue($this->getGrammar()) : $column;

return $this->firstOrFail([$column])->{Str::afterLast($column, '.')};
}







public function get($columns = ['*'])
{
$builder = $this->applyScopes();




if (count($models = $builder->getModels($columns)) > 0) {
$models = $builder->eagerLoadRelations($models);
}

return $this->applyAfterQueryCallbacks(
$builder->getModel()->newCollection($models)
);
}







public function getModels($columns = ['*'])
{
return $this->model->hydrate(
$this->query->get($columns)->all()
)->all();
}







public function eagerLoadRelations(array $models)
{
foreach ($this->eagerLoad as $name => $constraints) {



if (! str_contains($name, '.')) {
$models = $this->eagerLoadRelation($models, $name, $constraints);
}
}

return $models;
}









protected function eagerLoadRelation(array $models, $name, Closure $constraints)
{



$relation = $this->getRelation($name);

$relation->addEagerConstraints($models);

$constraints($relation);




return $relation->match(
$relation->initRelation($models, $name),
$relation->getEager(), $name
);
}







public function getRelation($name)
{



$relation = Relation::noConstraints(function () use ($name) {
try {
return $this->getModel()->newInstance()->$name();
} catch (BadMethodCallException) {
throw RelationNotFoundException::make($this->getModel(), $name);
}
});

$nested = $this->relationsNestedUnder($name);




if (count($nested) > 0) {
$relation->getQuery()->with($nested);
}

return $relation;
}







protected function relationsNestedUnder($relation)
{
$nested = [];




foreach ($this->eagerLoad as $name => $constraints) {
if ($this->isNestedUnder($relation, $name)) {
$nested[substr($name, strlen($relation.'.'))] = $constraints;
}
}

return $nested;
}








protected function isNestedUnder($relation, $name)
{
return str_contains($name, '.') && str_starts_with($name, $relation.'.');
}







public function afterQuery(Closure $callback)
{
$this->afterQueryCallbacks[] = $callback;

return $this;
}







public function applyAfterQueryCallbacks($result)
{
foreach ($this->afterQueryCallbacks as $afterQueryCallback) {
$result = $afterQueryCallback($result) ?: $result;
}

return $result;
}






public function cursor()
{
return $this->applyScopes()->query->cursor()->map(function ($record) {
$model = $this->newModelInstance()->newFromBuilder($record);

return $this->applyAfterQueryCallbacks($this->newModelInstance()->newCollection([$model]))->first();
})->reject(fn ($model) => is_null($model));
}






protected function enforceOrderBy()
{
if (empty($this->query->orders) && empty($this->query->unionOrders)) {
$this->orderBy($this->model->getQualifiedKeyName(), 'asc');
}
}








public function pluck($column, $key = null)
{
$results = $this->toBase()->pluck($column, $key);

$column = $column instanceof Expression ? $column->getValue($this->getGrammar()) : $column;

$column = Str::after($column, "{$this->model->getTable()}.");




if (! $this->model->hasGetMutator($column) &&
! $this->model->hasCast($column) &&
! in_array($column, $this->model->getDates())) {
return $results;
}

return $this->applyAfterQueryCallbacks(
$results->map(function ($value) use ($column) {
return $this->model->newFromBuilder([$column => $value])->{$column};
})
);
}













public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null)
{
$page = $page ?: Paginator::resolveCurrentPage($pageName);

$total = value($total) ?? $this->toBase()->getCountForPagination();

$perPage = ($perPage instanceof Closure
? $perPage($total)
: $perPage
) ?: $this->model->getPerPage();

$results = $total
? $this->forPage($page, $perPage)->get($columns)
: $this->model->newCollection();

return $this->paginator($results, $total, $perPage, $page, [
'path' => Paginator::resolveCurrentPath(),
'pageName' => $pageName,
]);
}










public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
{
$page = $page ?: Paginator::resolveCurrentPage($pageName);

$perPage = $perPage ?: $this->model->getPerPage();




$this->skip(($page - 1) * $perPage)->take($perPage + 1);

return $this->simplePaginator($this->get($columns), $perPage, $page, [
'path' => Paginator::resolveCurrentPath(),
'pageName' => $pageName,
]);
}










public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
{
$perPage = $perPage ?: $this->model->getPerPage();

return $this->paginateUsingCursor($perPage, $columns, $cursorName, $cursor);
}







protected function ensureOrderForCursorPagination($shouldReverse = false)
{
if (empty($this->query->orders) && empty($this->query->unionOrders)) {
$this->enforceOrderBy();
}

$reverseDirection = function ($order) {
if (! isset($order['direction'])) {
return $order;
}

$order['direction'] = $order['direction'] === 'asc' ? 'desc' : 'asc';

return $order;
};

if ($shouldReverse) {
$this->query->orders = collect($this->query->orders)->map($reverseDirection)->toArray();
$this->query->unionOrders = collect($this->query->unionOrders)->map($reverseDirection)->toArray();
}

$orders = ! empty($this->query->unionOrders) ? $this->query->unionOrders : $this->query->orders;

return collect($orders)
->filter(fn ($order) => Arr::has($order, 'direction'))
->values();
}







public function create(array $attributes = [])
{
return tap($this->newModelInstance($attributes), function ($instance) {
$instance->save();
});
}







public function forceCreate(array $attributes)
{
return $this->model->unguarded(function () use ($attributes) {
return $this->newModelInstance()->create($attributes);
});
}







public function forceCreateQuietly(array $attributes = [])
{
return Model::withoutEvents(fn () => $this->forceCreate($attributes));
}







public function update(array $values)
{
return $this->toBase()->update($this->addUpdatedAtColumn($values));
}









public function upsert(array $values, $uniqueBy, $update = null)
{
if (empty($values)) {
return 0;
}

if (! is_array(reset($values))) {
$values = [$values];
}

if (is_null($update)) {
$update = array_keys(reset($values));
}

return $this->toBase()->upsert(
$this->addTimestampsToUpsertValues($this->addUniqueIdsToUpsertValues($values)),
$uniqueBy,
$this->addUpdatedAtToUpsertColumns($update)
);
}







public function touch($column = null)
{
$time = $this->model->freshTimestamp();

if ($column) {
return $this->toBase()->update([$column => $time]);
}

$column = $this->model->getUpdatedAtColumn();

if (! $this->model->usesTimestamps() || is_null($column)) {
return false;
}

return $this->toBase()->update([$column => $time]);
}









public function increment($column, $amount = 1, array $extra = [])
{
return $this->toBase()->increment(
$column, $amount, $this->addUpdatedAtColumn($extra)
);
}









public function decrement($column, $amount = 1, array $extra = [])
{
return $this->toBase()->decrement(
$column, $amount, $this->addUpdatedAtColumn($extra)
);
}







protected function addUpdatedAtColumn(array $values)
{
if (! $this->model->usesTimestamps() ||
is_null($this->model->getUpdatedAtColumn())) {
return $values;
}

$column = $this->model->getUpdatedAtColumn();

if (! array_key_exists($column, $values)) {
$timestamp = $this->model->freshTimestampString();

if (
$this->model->hasSetMutator($column)
|| $this->model->hasAttributeSetMutator($column)
|| $this->model->hasCast($column)
) {
$timestamp = $this->model->newInstance()
->forceFill([$column => $timestamp])
->getAttributes()[$column] ?? $timestamp;
}

$values = array_merge([$column => $timestamp], $values);
}

$segments = preg_split('/\s+as\s+/i', $this->query->from);

$qualifiedColumn = end($segments).'.'.$column;

$values[$qualifiedColumn] = Arr::get($values, $qualifiedColumn, $values[$column]);

unset($values[$column]);

return $values;
}







protected function addUniqueIdsToUpsertValues(array $values)
{
if (! $this->model->usesUniqueIds()) {
return $values;
}

foreach ($this->model->uniqueIds() as $uniqueIdAttribute) {
foreach ($values as &$row) {
if (! array_key_exists($uniqueIdAttribute, $row)) {
$row = array_merge([$uniqueIdAttribute => $this->model->newUniqueId()], $row);
}
}
}

return $values;
}







protected function addTimestampsToUpsertValues(array $values)
{
if (! $this->model->usesTimestamps()) {
return $values;
}

$timestamp = $this->model->freshTimestampString();

$columns = array_filter([
$this->model->getCreatedAtColumn(),
$this->model->getUpdatedAtColumn(),
]);

foreach ($columns as $column) {
foreach ($values as &$row) {
$row = array_merge([$column => $timestamp], $row);
}
}

return $values;
}







protected function addUpdatedAtToUpsertColumns(array $update)
{
if (! $this->model->usesTimestamps()) {
return $update;
}

$column = $this->model->getUpdatedAtColumn();

if (! is_null($column) &&
! array_key_exists($column, $update) &&
! in_array($column, $update)) {
$update[] = $column;
}

return $update;
}






public function delete()
{
if (isset($this->onDelete)) {
return call_user_func($this->onDelete, $this);
}

return $this->toBase()->delete();
}








public function forceDelete()
{
return $this->query->delete();
}







public function onDelete(Closure $callback)
{
$this->onDelete = $callback;
}







public function hasNamedScope($scope)
{
return $this->model && $this->model->hasNamedScope($scope);
}







public function scopes($scopes)
{
$builder = $this;

foreach (Arr::wrap($scopes) as $scope => $parameters) {



if (is_int($scope)) {
[$scope, $parameters] = [$parameters, []];
}




$builder = $builder->callNamedScope(
$scope, Arr::wrap($parameters)
);
}

return $builder;
}






public function applyScopes()
{
if (! $this->scopes) {
return $this;
}

$builder = clone $this;

foreach ($this->scopes as $identifier => $scope) {
if (! isset($builder->scopes[$identifier])) {
continue;
}

$builder->callScope(function (self $builder) use ($scope) {



if ($scope instanceof Closure) {
$scope($builder);
}




if ($scope instanceof Scope) {
$scope->apply($builder, $this->getModel());
}
});
}

return $builder;
}








protected function callScope(callable $scope, array $parameters = [])
{
array_unshift($parameters, $this);

$query = $this->getQuery();




$originalWhereCount = is_null($query->wheres)
? 0 : count($query->wheres);

$result = $scope(...$parameters) ?? $this;

if (count((array) $query->wheres) > $originalWhereCount) {
$this->addNewWheresWithinGroup($query, $originalWhereCount);
}

return $result;
}








protected function callNamedScope($scope, array $parameters = [])
{
return $this->callScope(function (...$parameters) use ($scope) {
return $this->model->callNamedScope($scope, $parameters);
}, $parameters);
}








protected function addNewWheresWithinGroup(QueryBuilder $query, $originalWhereCount)
{



$allWheres = $query->wheres;

$query->wheres = [];

$this->groupWhereSliceForScope(
$query, array_slice($allWheres, 0, $originalWhereCount)
);

$this->groupWhereSliceForScope(
$query, array_slice($allWheres, $originalWhereCount)
);
}








protected function groupWhereSliceForScope(QueryBuilder $query, $whereSlice)
{
$whereBooleans = collect($whereSlice)->pluck('boolean');




if ($whereBooleans->contains(fn ($logicalOperator) => str_contains($logicalOperator, 'or'))) {
$query->wheres[] = $this->createNestedWhere(
$whereSlice, str_replace(' not', '', $whereBooleans->first())
);
} else {
$query->wheres = array_merge($query->wheres, $whereSlice);
}
}








protected function createNestedWhere($whereSlice, $boolean = 'and')
{
$whereGroup = $this->getQuery()->forNestedWhere();

$whereGroup->wheres = $whereSlice;

return ['type' => 'Nested', 'query' => $whereGroup, 'boolean' => $boolean];
}








public function with($relations, $callback = null)
{
if ($callback instanceof Closure) {
$eagerLoad = $this->parseWithRelations([$relations => $callback]);
} else {
$eagerLoad = $this->parseWithRelations(is_string($relations) ? func_get_args() : $relations);
}

$this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);

return $this;
}







public function without($relations)
{
$this->eagerLoad = array_diff_key($this->eagerLoad, array_flip(
is_string($relations) ? func_get_args() : $relations
));

return $this;
}







public function withOnly($relations)
{
$this->eagerLoad = [];

return $this->with($relations);
}







public function newModelInstance($attributes = [])
{
return $this->model->newInstance($attributes)->setConnection(
$this->query->getConnection()->getName()
);
}







protected function parseWithRelations(array $relations)
{
if ($relations === []) {
return [];
}

$results = [];

foreach ($this->prepareNestedWithRelationships($relations) as $name => $constraints) {



$results = $this->addNestedWiths($name, $results);

$results[$name] = $constraints;
}

return $results;
}








protected function prepareNestedWithRelationships($relations, $prefix = '')
{
$preparedRelationships = [];

if ($prefix !== '') {
$prefix .= '.';
}




foreach ($relations as $key => $value) {
if (! is_string($key) || ! is_array($value)) {
continue;
}

[$attribute, $attributeSelectConstraint] = $this->parseNameAndAttributeSelectionConstraint($key);

$preparedRelationships = array_merge(
$preparedRelationships,
["{$prefix}{$attribute}" => $attributeSelectConstraint],
$this->prepareNestedWithRelationships($value, "{$prefix}{$attribute}"),
);

unset($relations[$key]);
}




foreach ($relations as $key => $value) {
if (is_numeric($key) && is_string($value)) {
[$key, $value] = $this->parseNameAndAttributeSelectionConstraint($value);
}

$preparedRelationships[$prefix.$key] = $this->combineConstraints([
$value,
$preparedRelationships[$prefix.$key] ?? static function () {

},
]);
}

return $preparedRelationships;
}







protected function combineConstraints(array $constraints)
{
return function ($builder) use ($constraints) {
foreach ($constraints as $constraint) {
$builder = $constraint($builder) ?? $builder;
}

return $builder;
};
}







protected function parseNameAndAttributeSelectionConstraint($name)
{
return str_contains($name, ':')
? $this->createSelectWithConstraint($name)
: [$name, static function () {

}];
}







protected function createSelectWithConstraint($name)
{
return [explode(':', $name)[0], static function ($query) use ($name) {
$query->select(array_map(static function ($column) use ($query) {
if (str_contains($column, '.')) {
return $column;
}

return $query instanceof BelongsToMany
? $query->getRelated()->getTable().'.'.$column
: $column;
}, explode(',', explode(':', $name)[1])));
}];
}








protected function addNestedWiths($name, $results)
{
$progress = [];




foreach (explode('.', $name) as $segment) {
$progress[] = $segment;

if (! isset($results[$last = implode('.', $progress)])) {
$results[$last] = static function () {

};
}
}

return $results;
}







public function withCasts($casts)
{
$this->model->mergeCasts($casts);

return $this;
}

/**
@template





*/
public function withSavepointIfNeeded(Closure $scope): mixed
{
return $this->getQuery()->getConnection()->transactionLevel() > 0
? $this->getQuery()->getConnection()->transaction($scope)
: $scope();
}






protected function getUnionBuilders()
{
return isset($this->query->unions)
? collect($this->query->unions)->pluck('query')
: collect();
}






public function getQuery()
{
return $this->query;
}







public function setQuery($query)
{
$this->query = $query;

return $this;
}






public function toBase()
{
return $this->applyScopes()->getQuery();
}






public function getEagerLoads()
{
return $this->eagerLoad;
}







public function setEagerLoads(array $eagerLoad)
{
$this->eagerLoad = $eagerLoad;

return $this;
}







public function withoutEagerLoad(array $relations)
{
$relations = array_diff(array_keys($this->model->getRelations()), $relations);

return $this->with($relations);
}






public function withoutEagerLoads()
{
return $this->setEagerLoads([]);
}






protected function defaultKeyName()
{
return $this->getModel()->getKeyName();
}






public function getModel()
{
return $this->model;
}

/**
@template





*/
public function setModel(Model $model)
{
$this->model = $model;

$this->query->from($model->getTable());

return $this;
}







public function qualifyColumn($column)
{
$column = $column instanceof Expression ? $column->getValue($this->getGrammar()) : $column;

return $this->model->qualifyColumn($column);
}







public function qualifyColumns($columns)
{
return $this->model->qualifyColumns($columns);
}







public function getMacro($name)
{
return Arr::get($this->localMacros, $name);
}







public function hasMacro($name)
{
return isset($this->localMacros[$name]);
}







public static function getGlobalMacro($name)
{
return Arr::get(static::$macros, $name);
}







public static function hasGlobalMacro($name)
{
return isset(static::$macros[$name]);
}









public function __get($key)
{
if (in_array($key, ['orWhere', 'whereNot', 'orWhereNot'])) {
return new HigherOrderBuilderProxy($this, $key);
}

if (in_array($key, $this->propertyPassthru)) {
return $this->toBase()->{$key};
}

throw new Exception("Property [{$key}] does not exist on the Eloquent builder instance.");
}








public function __call($method, $parameters)
{
if ($method === 'macro') {
$this->localMacros[$parameters[0]] = $parameters[1];

return;
}

if ($this->hasMacro($method)) {
array_unshift($parameters, $this);

return $this->localMacros[$method](...$parameters);
}

if (static::hasGlobalMacro($method)) {
$callable = static::$macros[$method];

if ($callable instanceof Closure) {
$callable = $callable->bindTo($this, static::class);
}

return $callable(...$parameters);
}

if ($this->hasNamedScope($method)) {
return $this->callNamedScope($method, $parameters);
}

if (in_array(strtolower($method), $this->passthru)) {
return $this->toBase()->{$method}(...$parameters);
}

$this->forwardCallTo($this->query, $method, $parameters);

return $this;
}










public static function __callStatic($method, $parameters)
{
if ($method === 'macro') {
static::$macros[$parameters[0]] = $parameters[1];

return;
}

if ($method === 'mixin') {
return static::registerMixin($parameters[0], $parameters[1] ?? true);
}

if (! static::hasGlobalMacro($method)) {
static::throwBadMethodCallException($method);
}

$callable = static::$macros[$method];

if ($callable instanceof Closure) {
$callable = $callable->bindTo(null, static::class);
}

return $callable(...$parameters);
}








protected static function registerMixin($mixin, $replace)
{
$methods = (new ReflectionClass($mixin))->getMethods(
ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
);

foreach ($methods as $method) {
if ($replace || ! static::hasGlobalMacro($method->name)) {
static::macro($method->name, $method->invoke($mixin));
}
}
}






public function clone()
{
return clone $this;
}






public function __clone()
{
$this->query = clone $this->query;
}
}
