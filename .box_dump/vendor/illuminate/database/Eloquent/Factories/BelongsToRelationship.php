<?php

namespace Illuminate\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BelongsToRelationship
{





protected $factory;






protected $relationship;






protected $resolved;








public function __construct($factory, $relationship)
{
$this->factory = $factory;
$this->relationship = $relationship;
}







public function attributesFor(Model $model)
{
$relationship = $model->{$this->relationship}();

return $relationship instanceof MorphTo ? [
$relationship->getMorphType() => $this->factory instanceof Factory ? $this->factory->newModel()->getMorphClass() : $this->factory->getMorphClass(),
$relationship->getForeignKeyName() => $this->resolver($relationship->getOwnerKeyName()),
] : [
$relationship->getForeignKeyName() => $this->resolver($relationship->getOwnerKeyName()),
];
}







protected function resolver($key)
{
return function () use ($key) {
if (! $this->resolved) {
$instance = $this->factory instanceof Factory
? ($this->factory->getRandomRecycledModel($this->factory->modelName()) ?? $this->factory->create())
: $this->factory;

return $this->resolved = $key ? $instance->{$key} : $instance->getKey();
}

return $this->resolved;
};
}







public function recycle($recycle)
{
if ($this->factory instanceof Factory) {
$this->factory = $this->factory->recycle($recycle);
}

return $this;
}
}
