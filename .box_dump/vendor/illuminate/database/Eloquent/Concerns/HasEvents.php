<?php

namespace Illuminate\Database\Eloquent\Concerns;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Events\NullDispatcher;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use ReflectionClass;

trait HasEvents
{







protected $dispatchesEvents = [];








protected $observables = [];






public static function bootHasEvents()
{
static::observe(static::resolveObserveAttributes());
}






public static function resolveObserveAttributes()
{
$reflectionClass = new ReflectionClass(static::class);

return collect($reflectionClass->getAttributes(ObservedBy::class))
->map(fn ($attribute) => $attribute->getArguments())
->flatten()
->all();
}









public static function observe($classes)
{
$instance = new static;

foreach (Arr::wrap($classes) as $class) {
$instance->registerObserver($class);
}
}









protected function registerObserver($class)
{
$className = $this->resolveObserverClassName($class);




foreach ($this->getObservableEvents() as $event) {
if (method_exists($class, $event)) {
static::registerModelEvent($event, $className.'@'.$event);
}
}
}









private function resolveObserverClassName($class)
{
if (is_object($class)) {
return get_class($class);
}

if (class_exists($class)) {
return $class;
}

throw new InvalidArgumentException('Unable to find observer: '.$class);
}






public function getObservableEvents()
{
return array_merge(
[
'retrieved', 'creating', 'created', 'updating', 'updated',
'saving', 'saved', 'restoring', 'restored', 'replicating',
'deleting', 'deleted', 'forceDeleting', 'forceDeleted',
],
$this->observables
);
}







public function setObservableEvents(array $observables)
{
$this->observables = $observables;

return $this;
}







public function addObservableEvents($observables)
{
$this->observables = array_unique(array_merge(
$this->observables, is_array($observables) ? $observables : func_get_args()
));
}







public function removeObservableEvents($observables)
{
$this->observables = array_diff(
$this->observables, is_array($observables) ? $observables : func_get_args()
);
}








protected static function registerModelEvent($event, $callback)
{
if (isset(static::$dispatcher)) {
$name = static::class;

static::$dispatcher->listen("eloquent.{$event}: {$name}", $callback);
}
}








protected function fireModelEvent($event, $halt = true)
{
if (! isset(static::$dispatcher)) {
return true;
}




$method = $halt ? 'until' : 'dispatch';

$result = $this->filterModelEventResults(
$this->fireCustomModelEvent($event, $method)
);

if ($result === false) {
return false;
}

return ! empty($result) ? $result : static::$dispatcher->{$method}(
"eloquent.{$event}: ".static::class, $this
);
}








protected function fireCustomModelEvent($event, $method)
{
if (! isset($this->dispatchesEvents[$event])) {
return;
}

$result = static::$dispatcher->$method(new $this->dispatchesEvents[$event]($this));

if (! is_null($result)) {
return $result;
}
}







protected function filterModelEventResults($result)
{
if (is_array($result)) {
$result = array_filter($result, function ($response) {
return ! is_null($response);
});
}

return $result;
}







public static function retrieved($callback)
{
static::registerModelEvent('retrieved', $callback);
}







public static function saving($callback)
{
static::registerModelEvent('saving', $callback);
}







public static function saved($callback)
{
static::registerModelEvent('saved', $callback);
}







public static function updating($callback)
{
static::registerModelEvent('updating', $callback);
}







public static function updated($callback)
{
static::registerModelEvent('updated', $callback);
}







public static function creating($callback)
{
static::registerModelEvent('creating', $callback);
}







public static function created($callback)
{
static::registerModelEvent('created', $callback);
}







public static function replicating($callback)
{
static::registerModelEvent('replicating', $callback);
}







public static function deleting($callback)
{
static::registerModelEvent('deleting', $callback);
}







public static function deleted($callback)
{
static::registerModelEvent('deleted', $callback);
}






public static function flushEventListeners()
{
if (! isset(static::$dispatcher)) {
return;
}

$instance = new static;

foreach ($instance->getObservableEvents() as $event) {
static::$dispatcher->forget("eloquent.{$event}: ".static::class);
}

foreach (array_values($instance->dispatchesEvents) as $event) {
static::$dispatcher->forget($event);
}
}






public function dispatchesEvents()
{
return $this->dispatchesEvents;
}






public static function getEventDispatcher()
{
return static::$dispatcher;
}







public static function setEventDispatcher(Dispatcher $dispatcher)
{
static::$dispatcher = $dispatcher;
}






public static function unsetEventDispatcher()
{
static::$dispatcher = null;
}







public static function withoutEvents(callable $callback)
{
$dispatcher = static::getEventDispatcher();

if ($dispatcher) {
static::setEventDispatcher(new NullDispatcher($dispatcher));
}

try {
return $callback();
} finally {
if ($dispatcher) {
static::setEventDispatcher($dispatcher);
}
}
}
}
