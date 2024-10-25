<?php

namespace Illuminate\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

trait HasUuids
{





public function initializeHasUuids()
{
$this->usesUniqueIds = true;
}






public function uniqueIds()
{
return [$this->getKeyName()];
}






public function newUniqueId()
{
return (string) Str::orderedUuid();
}











public function resolveRouteBindingQuery($query, $value, $field = null)
{
if ($field && in_array($field, $this->uniqueIds()) && ! Str::isUuid($value)) {
throw (new ModelNotFoundException)->setModel(get_class($this), $value);
}

if (! $field && in_array($this->getRouteKeyName(), $this->uniqueIds()) && ! Str::isUuid($value)) {
throw (new ModelNotFoundException)->setModel(get_class($this), $value);
}

return parent::resolveRouteBindingQuery($query, $value, $field);
}






public function getKeyType()
{
if (in_array($this->getKeyName(), $this->uniqueIds())) {
return 'string';
}

return $this->keyType;
}






public function getIncrementing()
{
if (in_array($this->getKeyName(), $this->uniqueIds())) {
return false;
}

return $this->incrementing;
}
}
