<?php

namespace Illuminate\Http\Resources\Json;

use ArrayAccess;
use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
use Illuminate\Http\Resources\DelegatesToResource;
use JsonException;
use JsonSerializable;

class JsonResource implements ArrayAccess, JsonSerializable, Responsable, UrlRoutable
{
use ConditionallyLoadsAttributes, DelegatesToResource;






public $resource;






public $with = [];








public $additional = [];






public static $wrap = 'data';







public function __construct($resource)
{
$this->resource = $resource;
}







public static function make(...$parameters)
{
return new static(...$parameters);
}







public static function collection($resource)
{
return tap(static::newCollection($resource), function ($collection) {
if (property_exists(static::class, 'preserveKeys')) {
$collection->preserveKeys = (new static([]))->preserveKeys === true;
}
});
}







protected static function newCollection($resource)
{
return new AnonymousResourceCollection($resource, static::class);
}







public function resolve($request = null)
{
$data = $this->toArray(
$request ?: Container::getInstance()->make('request')
);

if ($data instanceof Arrayable) {
$data = $data->toArray();
} elseif ($data instanceof JsonSerializable) {
$data = $data->jsonSerialize();
}

return $this->filter((array) $data);
}







public function toArray(Request $request)
{
if (is_null($this->resource)) {
return [];
}

return is_array($this->resource)
? $this->resource
: $this->resource->toArray();
}









public function toJson($options = 0)
{
try {
$json = json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
throw JsonEncodingException::forResource($this, $e->getMessage());
}

return $json;
}







public function with(Request $request)
{
return $this->with;
}







public function additional(array $data)
{
$this->additional = $data;

return $this;
}






public function jsonOptions()
{
return 0;
}








public function withResponse(Request $request, JsonResponse $response)
{

}







public static function wrap($value)
{
static::$wrap = $value;
}






public static function withoutWrapping()
{
static::$wrap = null;
}







public function response($request = null)
{
return $this->toResponse(
$request ?: Container::getInstance()->make('request')
);
}







public function toResponse($request)
{
return (new ResourceResponse($this))->toResponse($request);
}






public function jsonSerialize(): array
{
return $this->resolve(Container::getInstance()->make('request'));
}
}
