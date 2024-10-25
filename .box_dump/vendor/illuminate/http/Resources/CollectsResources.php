<?php

namespace Illuminate\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LogicException;
use ReflectionClass;
use Traversable;

trait CollectsResources
{






protected function collectResource($resource)
{
if ($resource instanceof MissingValue) {
return $resource;
}

if (is_array($resource)) {
$resource = new Collection($resource);
}

$collects = $this->collects();

$this->collection = $collects && ! $resource->first() instanceof $collects
? $resource->mapInto($collects)
: $resource->toBase();

return ($resource instanceof AbstractPaginator || $resource instanceof AbstractCursorPaginator)
? $resource->setCollection($this->collection)
: $this->collection;
}






protected function collects()
{
$collects = null;

if ($this->collects) {
$collects = $this->collects;
} elseif (str_ends_with(class_basename($this), 'Collection') &&
(class_exists($class = Str::replaceLast('Collection', '', get_class($this))) ||
class_exists($class = Str::replaceLast('Collection', 'Resource', get_class($this))))) {
$collects = $class;
}

if (! $collects || is_a($collects, JsonResource::class, true)) {
return $collects;
}

throw new LogicException('Resource collections must collect instances of '.JsonResource::class.'.');
}








public function jsonOptions()
{
$collects = $this->collects();

if (! $collects) {
return 0;
}

return (new ReflectionClass($collects))
->newInstanceWithoutConstructor()
->jsonOptions();
}






public function getIterator(): Traversable
{
return $this->collection->getIterator();
}
}
