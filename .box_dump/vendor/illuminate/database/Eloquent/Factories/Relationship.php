<?php

namespace Illuminate\Database\Eloquent\Factories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;

class Relationship
{





protected $factory;






protected $relationship;








public function __construct(Factory $factory, $relationship)
{
$this->factory = $factory;
$this->relationship = $relationship;
}







public function createFor(Model $parent)
{
$relationship = $parent->{$this->relationship}();

if ($relationship instanceof MorphOneOrMany) {
$this->factory->state([
$relationship->getMorphType() => $relationship->getMorphClass(),
$relationship->getForeignKeyName() => $relationship->getParentKey(),
])->create([], $parent);
} elseif ($relationship instanceof HasOneOrMany) {
$this->factory->state([
$relationship->getForeignKeyName() => $relationship->getParentKey(),
])->create([], $parent);
} elseif ($relationship instanceof BelongsToMany) {
$relationship->attach($this->factory->create([], $parent));
}
}







public function recycle($recycle)
{
$this->factory = $this->factory->recycle($recycle);

return $this;
}
}
