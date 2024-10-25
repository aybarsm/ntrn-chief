<?php

namespace Illuminate\Database\Eloquent\Concerns;

use Illuminate\Support\Facades\Date;

trait HasTimestamps
{





public $timestamps = true;






protected static $ignoreTimestampsOn = [];







public function touch($attribute = null)
{
if ($attribute) {
$this->$attribute = $this->freshTimestamp();

return $this->save();
}

if (! $this->usesTimestamps()) {
return false;
}

$this->updateTimestamps();

return $this->save();
}







public function touchQuietly($attribute = null)
{
return static::withoutEvents(fn () => $this->touch($attribute));
}






public function updateTimestamps()
{
$time = $this->freshTimestamp();

$updatedAtColumn = $this->getUpdatedAtColumn();

if (! is_null($updatedAtColumn) && ! $this->isDirty($updatedAtColumn)) {
$this->setUpdatedAt($time);
}

$createdAtColumn = $this->getCreatedAtColumn();

if (! $this->exists && ! is_null($createdAtColumn) && ! $this->isDirty($createdAtColumn)) {
$this->setCreatedAt($time);
}

return $this;
}







public function setCreatedAt($value)
{
$this->{$this->getCreatedAtColumn()} = $value;

return $this;
}







public function setUpdatedAt($value)
{
$this->{$this->getUpdatedAtColumn()} = $value;

return $this;
}






public function freshTimestamp()
{
return Date::now();
}






public function freshTimestampString()
{
return $this->fromDateTime($this->freshTimestamp());
}






public function usesTimestamps()
{
return $this->timestamps && ! static::isIgnoringTimestamps($this::class);
}






public function getCreatedAtColumn()
{
return static::CREATED_AT;
}






public function getUpdatedAtColumn()
{
return static::UPDATED_AT;
}






public function getQualifiedCreatedAtColumn()
{
return $this->qualifyColumn($this->getCreatedAtColumn());
}






public function getQualifiedUpdatedAtColumn()
{
return $this->qualifyColumn($this->getUpdatedAtColumn());
}







public static function withoutTimestamps(callable $callback)
{
return static::withoutTimestampsOn([static::class], $callback);
}








public static function withoutTimestampsOn($models, $callback)
{
static::$ignoreTimestampsOn = array_values(array_merge(static::$ignoreTimestampsOn, $models));

try {
return $callback();
} finally {
foreach ($models as $model) {
if (($key = array_search($model, static::$ignoreTimestampsOn, true)) !== false) {
unset(static::$ignoreTimestampsOn[$key]);
}
}
}
}







public static function isIgnoringTimestamps($class = null)
{
$class ??= static::class;

foreach (static::$ignoreTimestampsOn as $ignoredClass) {
if ($class === $ignoredClass || is_subclass_of($class, $ignoredClass)) {
return true;
}
}

return false;
}
}
