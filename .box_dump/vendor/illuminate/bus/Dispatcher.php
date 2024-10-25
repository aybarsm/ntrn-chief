<?php

namespace Illuminate\Bus;

use Closure;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Collection;
use RuntimeException;

class Dispatcher implements QueueingDispatcher
{





protected $container;






protected $pipeline;






protected $pipes = [];






protected $handlers = [];






protected $queueResolver;








public function __construct(Container $container, ?Closure $queueResolver = null)
{
$this->container = $container;
$this->queueResolver = $queueResolver;
$this->pipeline = new Pipeline($container);
}







public function dispatch($command)
{
return $this->queueResolver && $this->commandShouldBeQueued($command)
? $this->dispatchToQueue($command)
: $this->dispatchNow($command);
}










public function dispatchSync($command, $handler = null)
{
if ($this->queueResolver &&
$this->commandShouldBeQueued($command) &&
method_exists($command, 'onConnection')) {
return $this->dispatchToQueue($command->onConnection('sync'));
}

return $this->dispatchNow($command, $handler);
}








public function dispatchNow($command, $handler = null)
{
$uses = class_uses_recursive($command);

if (in_array(InteractsWithQueue::class, $uses) &&
in_array(Queueable::class, $uses) &&
! $command->job) {
$command->setJob(new SyncJob($this->container, json_encode([]), 'sync', 'sync'));
}

if ($handler || $handler = $this->getCommandHandler($command)) {
$callback = function ($command) use ($handler) {
$method = method_exists($handler, 'handle') ? 'handle' : '__invoke';

return $handler->{$method}($command);
};
} else {
$callback = function ($command) {
$method = method_exists($command, 'handle') ? 'handle' : '__invoke';

return $this->container->call([$command, $method]);
};
}

return $this->pipeline->send($command)->through($this->pipes)->then($callback);
}







public function findBatch(string $batchId)
{
return $this->container->make(BatchRepository::class)->find($batchId);
}







public function batch($jobs)
{
return new PendingBatch($this->container, Collection::wrap($jobs));
}







public function chain($jobs)
{
$jobs = Collection::wrap($jobs);
$jobs = ChainedBatch::prepareNestedBatches($jobs);

return new PendingChain($jobs->shift(), $jobs->toArray());
}







public function hasCommandHandler($command)
{
return array_key_exists(get_class($command), $this->handlers);
}







public function getCommandHandler($command)
{
if ($this->hasCommandHandler($command)) {
return $this->container->make($this->handlers[get_class($command)]);
}

return false;
}







protected function commandShouldBeQueued($command)
{
return $command instanceof ShouldQueue;
}









public function dispatchToQueue($command)
{
$connection = $command->connection ?? null;

$queue = call_user_func($this->queueResolver, $connection);

if (! $queue instanceof Queue) {
throw new RuntimeException('Queue resolver did not return a Queue implementation.');
}

if (method_exists($command, 'queue')) {
return $command->queue($queue, $command);
}

return $this->pushCommandToQueue($queue, $command);
}








protected function pushCommandToQueue($queue, $command)
{
if (isset($command->queue, $command->delay)) {
return $queue->laterOn($command->queue, $command->delay, $command);
}

if (isset($command->queue)) {
return $queue->pushOn($command->queue, $command);
}

if (isset($command->delay)) {
return $queue->later($command->delay, $command);
}

return $queue->push($command);
}








public function dispatchAfterResponse($command, $handler = null)
{
$this->container->terminating(function () use ($command, $handler) {
$this->dispatchSync($command, $handler);
});
}







public function pipeThrough(array $pipes)
{
$this->pipes = $pipes;

return $this;
}







public function map(array $map)
{
$this->handlers = array_merge($this->handlers, $map);

return $this;
}
}
