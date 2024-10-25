<?php

namespace Illuminate\Database\Eloquent;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as BaseCollection;

/**
@mixin






*/
trait SoftDeletes
{





protected $forceDeleting = false;






public static function bootSoftDeletes()
{
static::addGlobalScope(new SoftDeletingScope);
}






public function initializeSoftDeletes()
{
if (! isset($this->casts[$this->getDeletedAtColumn()])) {
$this->casts[$this->getDeletedAtColumn()] = 'datetime';
}
}






public function forceDelete()
{
if ($this->fireModelEvent('forceDeleting') === false) {
return false;
}

$this->forceDeleting = true;

return tap($this->delete(), function ($deleted) {
$this->forceDeleting = false;

if ($deleted) {
$this->fireModelEvent('forceDeleted', false);
}
});
}






public function forceDeleteQuietly()
{
return static::withoutEvents(fn () => $this->forceDelete());
}







public static function forceDestroy($ids)
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

foreach ($instance->withTrashed()->whereIn($key, $ids)->get() as $model) {
if ($model->forceDelete()) {
$count++;
}
}

return $count;
}






protected function performDeleteOnModel()
{
if ($this->forceDeleting) {
return tap($this->setKeysForSaveQuery($this->newModelQuery())->forceDelete(), function () {
$this->exists = false;
});
}

return $this->runSoftDelete();
}






protected function runSoftDelete()
{
$query = $this->setKeysForSaveQuery($this->newModelQuery());

$time = $this->freshTimestamp();

$columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

$this->{$this->getDeletedAtColumn()} = $time;

if ($this->usesTimestamps() && ! is_null($this->getUpdatedAtColumn())) {
$this->{$this->getUpdatedAtColumn()} = $time;

$columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
}

$query->update($columns);

$this->syncOriginalAttributes(array_keys($columns));

$this->fireModelEvent('trashed', false);
}






public function restore()
{



if ($this->fireModelEvent('restoring') === false) {
return false;
}

$this->{$this->getDeletedAtColumn()} = null;




$this->exists = true;

$result = $this->save();

$this->fireModelEvent('restored', false);

return $result;
}






public function restoreQuietly()
{
return static::withoutEvents(fn () => $this->restore());
}






public function trashed()
{
return ! is_null($this->{$this->getDeletedAtColumn()});
}







public static function softDeleted($callback)
{
static::registerModelEvent('trashed', $callback);
}







public static function restoring($callback)
{
static::registerModelEvent('restoring', $callback);
}







public static function restored($callback)
{
static::registerModelEvent('restored', $callback);
}







public static function forceDeleting($callback)
{
static::registerModelEvent('forceDeleting', $callback);
}







public static function forceDeleted($callback)
{
static::registerModelEvent('forceDeleted', $callback);
}






public function isForceDeleting()
{
return $this->forceDeleting;
}






public function getDeletedAtColumn()
{
return defined(static::class.'::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
}






public function getQualifiedDeletedAtColumn()
{
return $this->qualifyColumn($this->getDeletedAtColumn());
}
}
