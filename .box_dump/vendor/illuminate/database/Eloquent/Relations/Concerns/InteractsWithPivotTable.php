<?php

namespace Illuminate\Database\Eloquent\Relations\Concerns;

use BackedEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection as BaseCollection;

trait InteractsWithPivotTable
{









public function toggle($ids, $touch = true)
{
$changes = [
'attached' => [], 'detached' => [],
];

$records = $this->formatRecordsList($this->parseIds($ids));




$detach = array_values(array_intersect(
$this->newPivotQuery()->pluck($this->relatedPivotKey)->all(),
array_keys($records)
));

if (count($detach) > 0) {
$this->detach($detach, false);

$changes['detached'] = $this->castKeys($detach);
}




$attach = array_diff_key($records, array_flip($detach));

if (count($attach) > 0) {
$this->attach($attach, [], false);

$changes['attached'] = array_keys($attach);
}




if ($touch && (count($changes['attached']) ||
count($changes['detached']))) {
$this->touchIfTouching();
}

return $changes;
}







public function syncWithoutDetaching($ids)
{
return $this->sync($ids, false);
}








public function sync($ids, $detaching = true)
{
$changes = [
'attached' => [], 'detached' => [], 'updated' => [],
];




$current = $this->getCurrentlyAttachedPivots()
->pluck($this->relatedPivotKey)->all();

$records = $this->formatRecordsList($this->parseIds($ids));




if ($detaching) {
$detach = array_diff($current, array_keys($records));

if (count($detach) > 0) {
$this->detach($detach, false);

$changes['detached'] = $this->castKeys($detach);
}
}




$changes = array_merge(
$changes, $this->attachNew($records, $current, false)
);




if (count($changes['attached']) ||
count($changes['updated']) ||
count($changes['detached'])) {
$this->touchIfTouching();
}

return $changes;
}









public function syncWithPivotValues($ids, array $values, bool $detaching = true)
{
return $this->sync(collect($this->parseIds($ids))->mapWithKeys(function ($id) use ($values) {
return [$id => $values];
}), $detaching);
}







protected function formatRecordsList(array $records)
{
return collect($records)->mapWithKeys(function ($attributes, $id) {
if (! is_array($attributes)) {
[$id, $attributes] = [$attributes, []];
}

if ($id instanceof BackedEnum) {
$id = $id->value;
}

return [$id => $attributes];
})->all();
}









protected function attachNew(array $records, array $current, $touch = true)
{
$changes = ['attached' => [], 'updated' => []];

foreach ($records as $id => $attributes) {



if (! in_array($id, $current)) {
$this->attach($id, $attributes, $touch);

$changes['attached'][] = $this->castKey($id);
}




elseif (count($attributes) > 0 &&
$this->updateExistingPivot($id, $attributes, $touch)) {
$changes['updated'][] = $this->castKey($id);
}
}

return $changes;
}









public function updateExistingPivot($id, array $attributes, $touch = true)
{
if ($this->using &&
empty($this->pivotWheres) &&
empty($this->pivotWhereIns) &&
empty($this->pivotWhereNulls)) {
return $this->updateExistingPivotUsingCustomClass($id, $attributes, $touch);
}

if ($this->hasPivotColumn($this->updatedAt())) {
$attributes = $this->addTimestampsToAttachment($attributes, true);
}

$updated = $this->newPivotStatementForId($this->parseId($id))->update(
$this->castAttributes($attributes)
);

if ($touch) {
$this->touchIfTouching();
}

return $updated;
}









protected function updateExistingPivotUsingCustomClass($id, array $attributes, $touch)
{
$pivot = $this->getCurrentlyAttachedPivots()
->where($this->foreignPivotKey, $this->parent->{$this->parentKey})
->where($this->relatedPivotKey, $this->parseId($id))
->first();

$updated = $pivot ? $pivot->fill($attributes)->isDirty() : false;

if ($updated) {
$pivot->save();
}

if ($touch) {
$this->touchIfTouching();
}

return (int) $updated;
}









public function attach($id, array $attributes = [], $touch = true)
{
if ($this->using) {
$this->attachUsingCustomClass($id, $attributes);
} else {



$this->newPivotStatement()->insert($this->formatAttachRecords(
$this->parseIds($id), $attributes
));
}

if ($touch) {
$this->touchIfTouching();
}
}








protected function attachUsingCustomClass($id, array $attributes)
{
$records = $this->formatAttachRecords(
$this->parseIds($id), $attributes
);

foreach ($records as $record) {
$this->newPivot($record, false)->save();
}
}








protected function formatAttachRecords($ids, array $attributes)
{
$records = [];

$hasTimestamps = ($this->hasPivotColumn($this->createdAt()) ||
$this->hasPivotColumn($this->updatedAt()));




foreach ($ids as $key => $value) {
$records[] = $this->formatAttachRecord(
$key, $value, $attributes, $hasTimestamps
);
}

return $records;
}










protected function formatAttachRecord($key, $value, $attributes, $hasTimestamps)
{
[$id, $attributes] = $this->extractAttachIdAndAttributes($key, $value, $attributes);

return array_merge(
$this->baseAttachRecord($id, $hasTimestamps), $this->castAttributes($attributes)
);
}









protected function extractAttachIdAndAttributes($key, $value, array $attributes)
{
return is_array($value)
? [$key, array_merge($value, $attributes)]
: [$value, $attributes];
}








protected function baseAttachRecord($id, $timed)
{
$record[$this->relatedPivotKey] = $id;

$record[$this->foreignPivotKey] = $this->parent->{$this->parentKey};




if ($timed) {
$record = $this->addTimestampsToAttachment($record);
}

foreach ($this->pivotValues as $value) {
$record[$value['column']] = $value['value'];
}

return $record;
}








protected function addTimestampsToAttachment(array $record, $exists = false)
{
$fresh = $this->parent->freshTimestamp();

if ($this->using) {
$pivotModel = new $this->using;

$fresh = $pivotModel->fromDateTime($fresh);
}

if (! $exists && $this->hasPivotColumn($this->createdAt())) {
$record[$this->createdAt()] = $fresh;
}

if ($this->hasPivotColumn($this->updatedAt())) {
$record[$this->updatedAt()] = $fresh;
}

return $record;
}







public function hasPivotColumn($column)
{
return in_array($column, $this->pivotColumns);
}








public function detach($ids = null, $touch = true)
{
if ($this->using &&
! empty($ids) &&
empty($this->pivotWheres) &&
empty($this->pivotWhereIns) &&
empty($this->pivotWhereNulls)) {
$results = $this->detachUsingCustomClass($ids);
} else {
$query = $this->newPivotQuery();




if (! is_null($ids)) {
$ids = $this->parseIds($ids);

if (empty($ids)) {
return 0;
}

$query->whereIn($this->getQualifiedRelatedPivotKeyName(), (array) $ids);
}




$results = $query->delete();
}

if ($touch) {
$this->touchIfTouching();
}

return $results;
}







protected function detachUsingCustomClass($ids)
{
$results = 0;

foreach ($this->parseIds($ids) as $id) {
$results += $this->newPivot([
$this->foreignPivotKey => $this->parent->{$this->parentKey},
$this->relatedPivotKey => $id,
], true)->delete();
}

return $results;
}






protected function getCurrentlyAttachedPivots()
{
return $this->newPivotQuery()->get()->map(function ($record) {
$class = $this->using ?: Pivot::class;

$pivot = $class::fromRawAttributes($this->parent, (array) $record, $this->getTable(), true);

return $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
});
}








public function newPivot(array $attributes = [], $exists = false)
{
$attributes = array_merge(array_column($this->pivotValues, 'value', 'column'), $attributes);

$pivot = $this->related->newPivot(
$this->parent, $attributes, $this->table, $exists, $this->using
);

return $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
}







public function newExistingPivot(array $attributes = [])
{
return $this->newPivot($attributes, true);
}






public function newPivotStatement()
{
return $this->query->getQuery()->newQuery()->from($this->table);
}







public function newPivotStatementForId($id)
{
return $this->newPivotQuery()->whereIn($this->relatedPivotKey, $this->parseIds($id));
}






public function newPivotQuery()
{
$query = $this->newPivotStatement();

foreach ($this->pivotWheres as $arguments) {
$query->where(...$arguments);
}

foreach ($this->pivotWhereIns as $arguments) {
$query->whereIn(...$arguments);
}

foreach ($this->pivotWhereNulls as $arguments) {
$query->whereNull(...$arguments);
}

return $query->where($this->getQualifiedForeignPivotKeyName(), $this->parent->{$this->parentKey});
}







public function withPivot($columns)
{
$this->pivotColumns = array_merge(
$this->pivotColumns, is_array($columns) ? $columns : func_get_args()
);

return $this;
}







protected function parseIds($value)
{
if ($value instanceof Model) {
return [$value->{$this->relatedKey}];
}

if ($value instanceof Collection) {
return $value->pluck($this->relatedKey)->all();
}

if ($value instanceof BaseCollection) {
return $value->toArray();
}

return (array) $value;
}







protected function parseId($value)
{
return $value instanceof Model ? $value->{$this->relatedKey} : $value;
}







protected function castKeys(array $keys)
{
return array_map(function ($v) {
return $this->castKey($v);
}, $keys);
}







protected function castKey($key)
{
return $this->getTypeSwapValue(
$this->related->getKeyType(),
$key
);
}







protected function castAttributes($attributes)
{
return $this->using
? $this->newPivot()->fill($attributes)->getAttributes()
: $attributes;
}








protected function getTypeSwapValue($type, $value)
{
return match (strtolower($type)) {
'int', 'integer' => (int) $value,
'real', 'float', 'double' => (float) $value,
'string' => (string) $value,
default => $value,
};
}
}
