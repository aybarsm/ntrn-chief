<?php

namespace Illuminate\Database\Eloquent\Relations\Concerns;

use Illuminate\Contracts\Database\Eloquent\SupportsPartialRelations;
use Illuminate\Database\Eloquent\Model;

trait ComparesRelatedModels
{






public function is($model)
{
$match = ! is_null($model) &&
$this->compareKeys($this->getParentKey(), $this->getRelatedKeyFrom($model)) &&
$this->related->getTable() === $model->getTable() &&
$this->related->getConnectionName() === $model->getConnectionName();

if ($match && $this instanceof SupportsPartialRelations && $this->isOneOfMany()) {
return $this->query
->whereKey($model->getKey())
->exists();
}

return $match;
}







public function isNot($model)
{
return ! $this->is($model);
}






abstract public function getParentKey();







abstract protected function getRelatedKeyFrom(Model $model);








protected function compareKeys($parentKey, $relatedKey)
{
if (empty($parentKey) || empty($relatedKey)) {
return false;
}

if (is_int($parentKey) || is_int($relatedKey)) {
return (int) $parentKey === (int) $relatedKey;
}

return $parentKey === $relatedKey;
}
}
