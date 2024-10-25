<?php

namespace Illuminate\Log\Context;

use __PHP_Incomplete_Class;
use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Log\Context\Events\ContextDehydrating as Dehydrating;
use Illuminate\Log\Context\Events\ContextHydrated as Hydrated;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use RuntimeException;
use Throwable;

class Repository
{
use Conditionable, Macroable, SerializesModels;






protected $events;






protected $data = [];






protected $hidden = [];






protected static $handleUnserializeExceptionsUsing;




public function __construct(Dispatcher $events)
{
$this->events = $events;
}







public function has($key)
{
return array_key_exists($key, $this->data);
}







public function hasHidden($key)
{
return array_key_exists($key, $this->hidden);
}






public function all()
{
return $this->data;
}






public function allHidden()
{
return $this->hidden;
}








public function get($key, $default = null)
{
return $this->data[$key] ?? value($default);
}








public function getHidden($key, $default = null)
{
return $this->hidden[$key] ?? value($default);
}








public function pull($key, $default = null)
{
return tap($this->get($key, $default), function () use ($key) {
$this->forget($key);
});
}








public function pullHidden($key, $default = null)
{
return tap($this->getHidden($key, $default), function () use ($key) {
$this->forgetHidden($key);
});
}







public function only($keys)
{
return array_intersect_key($this->data, array_flip($keys));
}







public function onlyHidden($keys)
{
return array_intersect_key($this->hidden, array_flip($keys));
}








public function add($key, $value = null)
{
$this->data = array_merge(
$this->data,
is_array($key) ? $key : [$key => $value]
);

return $this;
}








public function addHidden($key, #[\SensitiveParameter] $value = null)
{
$this->hidden = array_merge(
$this->hidden,
is_array($key) ? $key : [$key => $value]
);

return $this;
}







public function forget($key)
{
foreach ((array) $key as $k) {
unset($this->data[$k]);
}

return $this;
}







public function forgetHidden($key)
{
foreach ((array) $key as $k) {
unset($this->hidden[$k]);
}

return $this;
}








public function addIf($key, $value)
{
if (! $this->has($key)) {
$this->add($key, $value);
}

return $this;
}








public function addHiddenIf($key, #[\SensitiveParameter] $value)
{
if (! $this->hasHidden($key)) {
$this->addHidden($key, $value);
}

return $this;
}










public function push($key, ...$values)
{
if (! $this->isStackable($key)) {
throw new RuntimeException("Unable to push value onto context stack for key [{$key}].");
}

$this->data[$key] = [
...$this->data[$key] ?? [],
...$values,
];

return $this;
}










public function pushHidden($key, ...$values)
{
if (! $this->isHiddenStackable($key)) {
throw new RuntimeException("Unable to push value onto hidden context stack for key [{$key}].");
}

$this->hidden[$key] = [
...$this->hidden[$key] ?? [],
...$values,
];

return $this;
}











public function stackContains(string $key, mixed $value, bool $strict = false): bool
{
if (! $this->isStackable($key)) {
throw new RuntimeException("Given key [{$key}] is not a stack.");
}

if (! array_key_exists($key, $this->data)) {
return false;
}

if ($value instanceof Closure) {
return collect($this->data[$key])->contains($value);
}

return in_array($value, $this->data[$key], $strict);
}











public function hiddenStackContains(string $key, mixed $value, bool $strict = false): bool
{
if (! $this->isHiddenStackable($key)) {
throw new RuntimeException("Given key [{$key}] is not a stack.");
}

if (! array_key_exists($key, $this->hidden)) {
return false;
}

if ($value instanceof Closure) {
return collect($this->hidden[$key])->contains($value);
}

return in_array($value, $this->hidden[$key], $strict);
}







protected function isStackable($key)
{
return ! $this->has($key) ||
(is_array($this->data[$key]) && array_is_list($this->data[$key]));
}







protected function isHiddenStackable($key)
{
return ! $this->hasHidden($key) ||
(is_array($this->hidden[$key]) && array_is_list($this->hidden[$key]));
}






public function isEmpty()
{
return $this->all() === [] && $this->allHidden() === [];
}







public function dehydrating($callback)
{
$this->events->listen(fn (Dehydrating $event) => $callback($event->context));

return $this;
}







public function hydrated($callback)
{
$this->events->listen(fn (Hydrated $event) => $callback($event->context));

return $this;
}







public function handleUnserializeExceptionsUsing($callback)
{
static::$handleUnserializeExceptionsUsing = $callback;

return $this;
}






public function flush()
{
$this->data = [];
$this->hidden = [];

return $this;
}








public function dehydrate()
{
$instance = (new static($this->events))
->add($this->all())
->addHidden($this->allHidden());

$instance->events->dispatch(new Dehydrating($instance));

$serialize = fn ($value) => serialize($instance->getSerializedPropertyValue($value, withRelations: false));

return $instance->isEmpty() ? null : [
'data' => array_map($serialize, $instance->all()),
'hidden' => array_map($serialize, $instance->allHidden()),
];
}











public function hydrate($context)
{
$unserialize = function ($value, $key, $hidden) {
try {
return tap($this->getRestoredPropertyValue(unserialize($value)), function ($value) {
if ($value instanceof __PHP_Incomplete_Class) {
throw new RuntimeException('Value is incomplete class: '.json_encode($value));
}
});
} catch (Throwable $e) {
if (static::$handleUnserializeExceptionsUsing !== null) {
return (static::$handleUnserializeExceptionsUsing)($e, $key, $value, $hidden);
}

if ($e instanceof ModelNotFoundException) {
if (function_exists('report')) {
report($e);
}

return null;
}

throw $e;
}
};

[$data, $hidden] = [
collect($context['data'] ?? [])->map(fn ($value, $key) => $unserialize($value, $key, false))->all(),
collect($context['hidden'] ?? [])->map(fn ($value, $key) => $unserialize($value, $key, true))->all(),
];

$this->events->dispatch(new Hydrated(
$this->flush()->add($data)->addHidden($hidden)
));

return $this;
}
}
