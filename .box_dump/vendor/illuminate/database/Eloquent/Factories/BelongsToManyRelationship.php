<?php

namespace Illuminate\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BelongsToManyRelationship
{





protected $factory;






protected $pivot;






protected $relationship;









public function __construct($factory, $pivot, $relationship)
{
$this->factory = $factory;
$this->pivot = $pivot;
$this->relationship = $relationship;
}







public function createFor(Model $model)
{
Collection::wrap($this->factory instanceof Factory ? $this->factory->create([], $model) : $this->factory)->each(function ($attachable) use ($model) {
$model->{$this->relationship}()->attach(
$attachable,
is_callable($this->pivot) ? call_user_func($this->pivot, $model) : $this->pivot
);
});
}







public function recycle($recycle)
{
if ($this->factory instanceof Factory) {
$this->factory = $this->factory->recycle($recycle);
}

return $this;
}
}
