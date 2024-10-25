<?php

namespace Illuminate\Database\Eloquent\Relations;

class MorphPivot extends Pivot
{







protected $morphType;








protected $morphClass;







protected function setKeysForSaveQuery($query)
{
$query->where($this->morphType, $this->morphClass);

return parent::setKeysForSaveQuery($query);
}







protected function setKeysForSelectQuery($query)
{
$query->where($this->morphType, $this->morphClass);

return parent::setKeysForSelectQuery($query);
}






public function delete()
{
if (isset($this->attributes[$this->getKeyName()])) {
return (int) parent::delete();
}

if ($this->fireModelEvent('deleting') === false) {
return 0;
}

$query = $this->getDeleteQuery();

$query->where($this->morphType, $this->morphClass);

return tap($query->delete(), function () {
$this->exists = false;

$this->fireModelEvent('deleted', false);
});
}






public function getMorphType()
{
return $this->morphType;
}







public function setMorphType($morphType)
{
$this->morphType = $morphType;

return $this;
}







public function setMorphClass($morphClass)
{
$this->morphClass = $morphClass;

return $this;
}






public function getQueueableId()
{
if (isset($this->attributes[$this->getKeyName()])) {
return $this->getKey();
}

return sprintf(
'%s:%s:%s:%s:%s:%s',
$this->foreignKey, $this->getAttribute($this->foreignKey),
$this->relatedKey, $this->getAttribute($this->relatedKey),
$this->morphType, $this->morphClass
);
}







public function newQueryForRestoration($ids)
{
if (is_array($ids)) {
return $this->newQueryForCollectionRestoration($ids);
}

if (! str_contains($ids, ':')) {
return parent::newQueryForRestoration($ids);
}

$segments = explode(':', $ids);

return $this->newQueryWithoutScopes()
->where($segments[0], $segments[1])
->where($segments[2], $segments[3])
->where($segments[4], $segments[5]);
}







protected function newQueryForCollectionRestoration(array $ids)
{
$ids = array_values($ids);

if (! str_contains($ids[0], ':')) {
return parent::newQueryForRestoration($ids);
}

$query = $this->newQueryWithoutScopes();

foreach ($ids as $id) {
$segments = explode(':', $id);

$query->orWhere(function ($query) use ($segments) {
return $query->where($segments[0], $segments[1])
->where($segments[2], $segments[3])
->where($segments[4], $segments[5]);
});
}

return $query;
}
}
