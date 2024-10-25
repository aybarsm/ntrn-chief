<?php

namespace Illuminate\Bus;

use Closure;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert as PHPUnit;
use RuntimeException;

use function Illuminate\Support\enum_value;

trait Queueable
{





public $connection;






public $queue;






public $delay;






public $afterCommit;






public $middleware = [];






public $chained = [];






public $chainConnection;






public $chainQueue;






public $chainCatchCallbacks;







public function onConnection($connection)
{
$this->connection = enum_value($connection);

return $this;
}







public function onQueue($queue)
{
$this->queue = enum_value($queue);

return $this;
}







public function allOnConnection($connection)
{
$resolvedConnection = enum_value($connection);

$this->chainConnection = $resolvedConnection;
$this->connection = $resolvedConnection;

return $this;
}







public function allOnQueue($queue)
{
$resolvedQueue = enum_value($queue);

$this->chainQueue = $resolvedQueue;
$this->queue = $resolvedQueue;

return $this;
}







public function delay($delay)
{
$this->delay = $delay;

return $this;
}






public function withoutDelay()
{
$this->delay = 0;

return $this;
}






public function afterCommit()
{
$this->afterCommit = true;

return $this;
}






public function beforeCommit()
{
$this->afterCommit = false;

return $this;
}







public function through($middleware)
{
$this->middleware = Arr::wrap($middleware);

return $this;
}







public function chain($chain)
{
$jobs = ChainedBatch::prepareNestedBatches(collect($chain));

$this->chained = $jobs->map(function ($job) {
return $this->serializeJob($job);
})->all();

return $this;
}







public function prependToChain($job)
{
$jobs = ChainedBatch::prepareNestedBatches(collect([$job]));

$this->chained = Arr::prepend($this->chained, $this->serializeJob($jobs->first()));

return $this;
}







public function appendToChain($job)
{
$jobs = ChainedBatch::prepareNestedBatches(collect([$job]));

$this->chained = array_merge($this->chained, [$this->serializeJob($jobs->first())]);

return $this;
}









protected function serializeJob($job)
{
if ($job instanceof Closure) {
if (! class_exists(CallQueuedClosure::class)) {
throw new RuntimeException(
'To enable support for closure jobs, please install the illuminate/queue package.'
);
}

$job = CallQueuedClosure::create($job);
}

return serialize($job);
}






public function dispatchNextJobInChain()
{
if (! empty($this->chained)) {
dispatch(tap(unserialize(array_shift($this->chained)), function ($next) {
$next->chained = $this->chained;

$next->onConnection($next->connection ?: $this->chainConnection);
$next->onQueue($next->queue ?: $this->chainQueue);

$next->chainConnection = $this->chainConnection;
$next->chainQueue = $this->chainQueue;
$next->chainCatchCallbacks = $this->chainCatchCallbacks;
}));
}
}







public function invokeChainCatchCallbacks($e)
{
collect($this->chainCatchCallbacks)->each(function ($callback) use ($e) {
$callback($e);
});
}







public function assertHasChain($expectedChain)
{
PHPUnit::assertTrue(
collect($expectedChain)->isNotEmpty(),
'The expected chain can not be empty.'
);

if (collect($expectedChain)->contains(fn ($job) => is_object($job))) {
$expectedChain = collect($expectedChain)->map(fn ($job) => serialize($job))->all();
} else {
$chain = collect($this->chained)->map(fn ($job) => get_class(unserialize($job)))->all();
}

PHPUnit::assertTrue(
$expectedChain === ($chain ?? $this->chained),
'The job does not have the expected chain.'
);
}






public function assertDoesntHaveChain()
{
PHPUnit::assertEmpty($this->chained, 'The job has chained jobs.');
}
}
