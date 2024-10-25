<?php

namespace Illuminate\Database\Eloquent\Relations\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait AsPivot
{





public $pivotParent;






protected $foreignKey;






protected $relatedKey;










public static function fromAttributes(Model $parent, $attributes, $table, $exists = false)
{
$instance = new static;

$instance->timestamps = $instance->hasTimestampAttributes($attributes);




$instance->setConnection($parent->getConnectionName())
->setTable($table)
->forceFill($attributes)
->syncOriginal();




$instance->pivotParent = $parent;

$instance->exists = $exists;

return $instance;
}










public static function fromRawAttributes(Model $parent, $attributes, $table, $exists = false)
{
$instance = static::fromAttributes($parent, [], $table, $exists);

$instance->timestamps = $instance->hasTimestampAttributes($attributes);

$instance->setRawAttributes(
array_merge($instance->getRawOriginal(), $attributes), $exists
);

return $instance;
}







protected function setKeysForSelectQuery($query)
{
if (isset($this->attributes[$this->getKeyName()])) {
return parent::setKeysForSelectQuery($query);
}

$query->where($this->foreignKey, $this->getOriginal(
$this->foreignKey, $this->getAttribute($this->foreignKey)
));

return $query->where($this->relatedKey, $this->getOriginal(
$this->relatedKey, $this->getAttribute($this->relatedKey)
));
}







protected function setKeysForSaveQuery($query)
{
return $this->setKeysForSelectQuery($query);
}






public function delete()
{
if (isset($this->attributes[$this->getKeyName()])) {
return (int) parent::delete();
}

if ($this->fireModelEvent('deleting') === false) {
return 0;
}

$this->touchOwners();

return tap($this->getDeleteQuery()->delete(), function () {
$this->exists = false;

$this->fireModelEvent('deleted', false);
});
}






protected function getDeleteQuery()
{
return $this->newQueryWithoutRelationships()->where([
$this->foreignKey => $this->getOriginal($this->foreignKey, $this->getAttribute($this->foreignKey)),
$this->relatedKey => $this->getOriginal($this->relatedKey, $this->getAttribute($this->relatedKey)),
]);
}






public function getTable()
{
if (! isset($this->table)) {
$this->setTable(str_replace(
'\\', '', Str::snake(Str::singular(class_basename($this)))
));
}

return $this->table;
}






public function getForeignKey()
{
return $this->foreignKey;
}






public function getRelatedKey()
{
return $this->relatedKey;
}






public function getOtherKey()
{
return $this->getRelatedKey();
}








public function setPivotKeys($foreignKey, $relatedKey)
{
$this->foreignKey = $foreignKey;

$this->relatedKey = $relatedKey;

return $this;
}







public function hasTimestampAttributes($attributes = null)
{
return array_key_exists($this->getCreatedAtColumn(), $attributes ?? $this->attributes);
}






public function getCreatedAtColumn()
{
return $this->pivotParent
? $this->pivotParent->getCreatedAtColumn()
: parent::getCreatedAtColumn();
}






public function getUpdatedAtColumn()
{
return $this->pivotParent
? $this->pivotParent->getUpdatedAtColumn()
: parent::getUpdatedAtColumn();
}






public function getQueueableId()
{
if (isset($this->attributes[$this->getKeyName()])) {
return $this->getKey();
}

return sprintf(
'%s:%s:%s:%s',
$this->foreignKey, $this->getAttribute($this->foreignKey),
$this->relatedKey, $this->getAttribute($this->relatedKey)
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
->where($segments[2], $segments[3]);
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
->where($segments[2], $segments[3]);
});
}

return $query;
}






public function unsetRelations()
{
$this->pivotParent = null;
$this->relations = [];

return $this;
}
}
