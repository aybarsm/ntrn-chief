<?php

namespace Illuminate\Redis\Connections;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Redis\Events\CommandExecuted;
use Illuminate\Redis\Limiters\ConcurrencyLimiterBuilder;
use Illuminate\Redis\Limiters\DurationLimiterBuilder;
use Illuminate\Support\Traits\Macroable;

abstract class Connection
{
use Macroable {
__call as macroCall;
}






protected $client;






protected $name;






protected $events;









abstract public function createSubscription($channels, Closure $callback, $method = 'subscribe');







public function funnel($name)
{
return new ConcurrencyLimiterBuilder($this, $name);
}







public function throttle($name)
{
return new DurationLimiterBuilder($this, $name);
}






public function client()
{
return $this->client;
}








public function subscribe($channels, Closure $callback)
{
$this->createSubscription($channels, $callback, __FUNCTION__);
}








public function psubscribe($channels, Closure $callback)
{
$this->createSubscription($channels, $callback, __FUNCTION__);
}








public function command($method, array $parameters = [])
{
$start = microtime(true);

$result = $this->client->{$method}(...$parameters);

$time = round((microtime(true) - $start) * 1000, 2);

$this->events?->dispatch(new CommandExecuted(
$method, $this->parseParametersForEvent($parameters), $time, $this
));

return $result;
}







protected function parseParametersForEvent(array $parameters)
{
return $parameters;
}









protected function event($event)
{
$this->events?->dispatch($event);
}







public function listen(Closure $callback)
{
$this->events?->listen(CommandExecuted::class, $callback);
}






public function getName()
{
return $this->name;
}







public function setName($name)
{
$this->name = $name;

return $this;
}






public function getEventDispatcher()
{
return $this->events;
}







public function setEventDispatcher(Dispatcher $events)
{
$this->events = $events;
}






public function unsetEventDispatcher()
{
$this->events = null;
}








public function __call($method, $parameters)
{
if (static::hasMacro($method)) {
return $this->macroCall($method, $parameters);
}

return $this->command($method, $parameters);
}
}
