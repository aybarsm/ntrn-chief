<?php

namespace Illuminate\Database\Eloquent;

use BadMethodCallException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Support\Str;

/**
@template
@template
*/
class PendingHasThroughRelationship
{





protected $rootModel;






protected $localRelationship;







public function __construct($rootModel, $localRelationship)
{
$this->rootModel = $rootModel;

$this->localRelationship = $localRelationship;
}

/**
@template













*/
public function has($callback)
{
if (is_string($callback)) {
$callback = fn () => $this->localRelationship->getRelated()->{$callback}();
}

$distantRelation = $callback($this->localRelationship->getRelated());

if ($distantRelation instanceof HasMany) {
$returnedRelation = $this->rootModel->hasManyThrough(
$distantRelation->getRelated()::class,
$this->localRelationship->getRelated()::class,
$this->localRelationship->getForeignKeyName(),
$distantRelation->getForeignKeyName(),
$this->localRelationship->getLocalKeyName(),
$distantRelation->getLocalKeyName(),
);
} else {
$returnedRelation = $this->rootModel->hasOneThrough(
$distantRelation->getRelated()::class,
$this->localRelationship->getRelated()::class,
$this->localRelationship->getForeignKeyName(),
$distantRelation->getForeignKeyName(),
$this->localRelationship->getLocalKeyName(),
$distantRelation->getLocalKeyName(),
);
}

if ($this->localRelationship instanceof MorphOneOrMany) {
$returnedRelation->where($this->localRelationship->getQualifiedMorphType(), $this->localRelationship->getMorphClass());
}

return $returnedRelation;
}








public function __call($method, $parameters)
{
if (Str::startsWith($method, 'has')) {
return $this->has(Str::of($method)->after('has')->lcfirst()->toString());
}

throw new BadMethodCallException(sprintf(
'Call to undefined method %s::%s()', static::class, $method
));
}
}
