<?php

namespace Illuminate\Http\Resources\Json;

use Countable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\CollectsResources;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use IteratorAggregate;

class ResourceCollection extends JsonResource implements Countable, IteratorAggregate
{
use CollectsResources;






public $collects;






public $collection;






protected $preserveAllQueryParameters = false;






protected $queryParameters;







public function __construct($resource)
{
parent::__construct($resource);

$this->resource = $this->collectResource($resource);
}






public function preserveQuery()
{
$this->preserveAllQueryParameters = true;

return $this;
}







public function withQuery(array $query)
{
$this->preserveAllQueryParameters = false;

$this->queryParameters = $query;

return $this;
}






public function count(): int
{
return $this->collection->count();
}







public function toArray(Request $request)
{
return $this->collection->map->toArray($request)->all();
}







public function toResponse($request)
{
if ($this->resource instanceof AbstractPaginator || $this->resource instanceof AbstractCursorPaginator) {
return $this->preparePaginatedResponse($request);
}

return parent::toResponse($request);
}







protected function preparePaginatedResponse($request)
{
if ($this->preserveAllQueryParameters) {
$this->resource->appends($request->query());
} elseif (! is_null($this->queryParameters)) {
$this->resource->appends($this->queryParameters);
}

return (new PaginatedResourceResponse($this))->toResponse($request);
}
}
