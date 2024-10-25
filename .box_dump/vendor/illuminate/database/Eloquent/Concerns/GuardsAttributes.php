<?php

namespace Illuminate\Database\Eloquent\Concerns;

trait GuardsAttributes
{





protected $fillable = [];






protected $guarded = ['*'];






protected static $unguarded = false;






protected static $guardableColumns = [];






public function getFillable()
{
return $this->fillable;
}







public function fillable(array $fillable)
{
$this->fillable = $fillable;

return $this;
}







public function mergeFillable(array $fillable)
{
$this->fillable = array_values(array_unique(array_merge($this->fillable, $fillable)));

return $this;
}






public function getGuarded()
{
return $this->guarded === false
? []
: $this->guarded;
}







public function guard(array $guarded)
{
$this->guarded = $guarded;

return $this;
}







public function mergeGuarded(array $guarded)
{
$this->guarded = array_values(array_unique(array_merge($this->guarded, $guarded)));

return $this;
}







public static function unguard($state = true)
{
static::$unguarded = $state;
}






public static function reguard()
{
static::$unguarded = false;
}






public static function isUnguarded()
{
return static::$unguarded;
}







public static function unguarded(callable $callback)
{
if (static::$unguarded) {
return $callback();
}

static::unguard();

try {
return $callback();
} finally {
static::reguard();
}
}







public function isFillable($key)
{
if (static::$unguarded) {
return true;
}




if (in_array($key, $this->getFillable())) {
return true;
}




if ($this->isGuarded($key)) {
return false;
}

return empty($this->getFillable()) &&
! str_contains($key, '.') &&
! str_starts_with($key, '_');
}







public function isGuarded($key)
{
if (empty($this->getGuarded())) {
return false;
}

return $this->getGuarded() == ['*'] ||
! empty(preg_grep('/^'.preg_quote($key, '/').'$/i', $this->getGuarded())) ||
! $this->isGuardableColumn($key);
}







protected function isGuardableColumn($key)
{
if ($this->hasSetMutator($key) || $this->hasAttributeSetMutator($key)) {
return true;
}

if (! isset(static::$guardableColumns[get_class($this)])) {
$columns = $this->getConnection()
->getSchemaBuilder()
->getColumnListing($this->getTable());

if (empty($columns)) {
return true;
}

static::$guardableColumns[get_class($this)] = $columns;
}

return in_array($key, static::$guardableColumns[get_class($this)]);
}






public function totallyGuarded()
{
return count($this->getFillable()) === 0 && $this->getGuarded() == ['*'];
}







protected function fillableFromArray(array $attributes)
{
if (count($this->getFillable()) > 0 && ! static::$unguarded) {
return array_intersect_key($attributes, array_flip($this->getFillable()));
}

return $attributes;
}
}
