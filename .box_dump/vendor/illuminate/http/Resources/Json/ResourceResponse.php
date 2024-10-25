<?php

namespace Illuminate\Http\Resources\Json;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ResourceResponse implements Responsable
{





public $resource;







public function __construct($resource)
{
$this->resource = $resource;
}







public function toResponse($request)
{
return tap(response()->json(
$this->wrap(
$this->resource->resolve($request),
$this->resource->with($request),
$this->resource->additional
),
$this->calculateStatus(),
[],
$this->resource->jsonOptions()
), function ($response) use ($request) {
$response->original = $this->resource->resource;

$this->resource->withResponse($request, $response);
});
}









protected function wrap($data, $with = [], $additional = [])
{
if ($data instanceof Collection) {
$data = $data->all();
}

if ($this->haveDefaultWrapperAndDataIsUnwrapped($data)) {
$data = [$this->wrapper() => $data];
} elseif ($this->haveAdditionalInformationAndDataIsUnwrapped($data, $with, $additional)) {
$data = [($this->wrapper() ?? 'data') => $data];
}

return array_merge_recursive($data, $with, $additional);
}







protected function haveDefaultWrapperAndDataIsUnwrapped($data)
{
return $this->wrapper() && ! array_key_exists($this->wrapper(), $data);
}









protected function haveAdditionalInformationAndDataIsUnwrapped($data, $with, $additional)
{
return (! empty($with) || ! empty($additional)) &&
(! $this->wrapper() ||
! array_key_exists($this->wrapper(), $data));
}






protected function wrapper()
{
return get_class($this->resource)::$wrap;
}






protected function calculateStatus()
{
return $this->resource->resource instanceof Model &&
$this->resource->resource->wasRecentlyCreated ? 201 : 200;
}
}
