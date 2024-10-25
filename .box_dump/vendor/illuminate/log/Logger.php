<?php

namespace Illuminate\Log;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Traits\Conditionable;
use Psr\Log\LoggerInterface;
use RuntimeException;

class Logger implements LoggerInterface
{
use Conditionable;






protected $logger;






protected $dispatcher;






protected $context = [];








public function __construct(LoggerInterface $logger, ?Dispatcher $dispatcher = null)
{
$this->logger = $logger;
$this->dispatcher = $dispatcher;
}








public function emergency($message, array $context = []): void
{
$this->writeLog(__FUNCTION__, $message, $context);
}








public function alert($message, array $context = []): void
{
$this->writeLog(__FUNCTION__, $message, $context);
}








public function critical($message, array $context = []): void
{
$this->writeLog(__FUNCTION__, $message, $context);
}








public function error($message, array $context = []): void
{
$this->writeLog(__FUNCTION__, $message, $context);
}








public function warning($message, array $context = []): void
{
$this->writeLog(__FUNCTION__, $message, $context);
}








public function notice($message, array $context = []): void
{
$this->writeLog(__FUNCTION__, $message, $context);
}








public function info($message, array $context = []): void
{
$this->writeLog(__FUNCTION__, $message, $context);
}








public function debug($message, array $context = []): void
{
$this->writeLog(__FUNCTION__, $message, $context);
}









public function log($level, $message, array $context = []): void
{
$this->writeLog($level, $message, $context);
}









public function write($level, $message, array $context = []): void
{
$this->writeLog($level, $message, $context);
}









protected function writeLog($level, $message, $context): void
{
$this->logger->{$level}(
$message = $this->formatMessage($message),
$context = array_merge($this->context, $context)
);

$this->fireLogEvent($level, $message, $context);
}







public function withContext(array $context = [])
{
$this->context = array_merge($this->context, $context);

return $this;
}






public function withoutContext()
{
$this->context = [];

return $this;
}









public function listen(Closure $callback)
{
if (! isset($this->dispatcher)) {
throw new RuntimeException('Events dispatcher has not been set.');
}

$this->dispatcher->listen(MessageLogged::class, $callback);
}









protected function fireLogEvent($level, $message, array $context = [])
{



$this->dispatcher?->dispatch(new MessageLogged($level, $message, $context));
}







protected function formatMessage($message)
{
if (is_array($message)) {
return var_export($message, true);
} elseif ($message instanceof Jsonable) {
return $message->toJson();
} elseif ($message instanceof Arrayable) {
return var_export($message->toArray(), true);
}

return (string) $message;
}






public function getLogger()
{
return $this->logger;
}






public function getEventDispatcher()
{
return $this->dispatcher;
}







public function setEventDispatcher(Dispatcher $dispatcher)
{
$this->dispatcher = $dispatcher;
}








public function __call($method, $parameters)
{
return $this->logger->{$method}(...$parameters);
}
}
