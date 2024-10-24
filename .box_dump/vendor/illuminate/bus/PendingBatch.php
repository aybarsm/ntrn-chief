<?php

namespace Illuminate\Bus;

use Closure;
use Illuminate\Bus\Events\BatchDispatched;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use Laravel\SerializableClosure\SerializableClosure;
use Throwable;

class PendingBatch
{
use Conditionable;






protected $container;






public $name = '';






public $jobs;






public $options = [];








public function __construct(Container $container, Collection $jobs)
{
$this->container = $container;
$this->jobs = $jobs;
}







public function add($jobs)
{
$jobs = is_iterable($jobs) ? $jobs : Arr::wrap($jobs);

foreach ($jobs as $job) {
$this->jobs->push($job);
}

return $this;
}







public function before($callback)
{
$this->options['before'][] = $callback instanceof Closure
? new SerializableClosure($callback)
: $callback;

return $this;
}






public function beforeCallbacks()
{
return $this->options['before'] ?? [];
}







public function progress($callback)
{
$this->options['progress'][] = $callback instanceof Closure
? new SerializableClosure($callback)
: $callback;

return $this;
}






public function progressCallbacks()
{
return $this->options['progress'] ?? [];
}







public function then($callback)
{
$this->options['then'][] = $callback instanceof Closure
? new SerializableClosure($callback)
: $callback;

return $this;
}






public function thenCallbacks()
{
return $this->options['then'] ?? [];
}







public function catch($callback)
{
$this->options['catch'][] = $callback instanceof Closure
? new SerializableClosure($callback)
: $callback;

return $this;
}






public function catchCallbacks()
{
return $this->options['catch'] ?? [];
}







public function finally($callback)
{
$this->options['finally'][] = $callback instanceof Closure
? new SerializableClosure($callback)
: $callback;

return $this;
}






public function finallyCallbacks()
{
return $this->options['finally'] ?? [];
}







public function allowFailures($allowFailures = true)
{
$this->options['allowFailures'] = $allowFailures;

return $this;
}






public function allowsFailures()
{
return Arr::get($this->options, 'allowFailures', false) === true;
}







public function name(string $name)
{
$this->name = $name;

return $this;
}







public function onConnection(string $connection)
{
$this->options['connection'] = $connection;

return $this;
}






public function connection()
{
return $this->options['connection'] ?? null;
}







public function onQueue(string $queue)
{
$this->options['queue'] = $queue;

return $this;
}






public function queue()
{
return $this->options['queue'] ?? null;
}








public function withOption(string $key, $value)
{
$this->options[$key] = $value;

return $this;
}








public function dispatch()
{
$repository = $this->container->make(BatchRepository::class);

try {
$batch = $this->store($repository);

$batch = $batch->add($this->jobs);
} catch (Throwable $e) {
if (isset($batch)) {
$repository->delete($batch->id);
}

throw $e;
}

$this->container->make(EventDispatcher::class)->dispatch(
new BatchDispatched($batch)
);

return $batch;
}






public function dispatchAfterResponse()
{
$repository = $this->container->make(BatchRepository::class);

$batch = $this->store($repository);

if ($batch) {
$this->container->terminating(function () use ($batch) {
$this->dispatchExistingBatch($batch);
});
}

return $batch;
}









protected function dispatchExistingBatch($batch)
{
try {
$batch = $batch->add($this->jobs);
} catch (Throwable $e) {
$batch->delete();

throw $e;
}

$this->container->make(EventDispatcher::class)->dispatch(
new BatchDispatched($batch)
);
}







public function dispatchIf($boolean)
{
return value($boolean) ? $this->dispatch() : null;
}







public function dispatchUnless($boolean)
{
return ! value($boolean) ? $this->dispatch() : null;
}







protected function store($repository)
{
$batch = $repository->store($this);

collect($this->beforeCallbacks())->each(function ($handler) use ($batch) {
try {
return $handler($batch);
} catch (Throwable $e) {
if (function_exists('report')) {
report($e);
}
}
});

return $batch;
}
}
