<?php










namespace Symfony\Component\HttpKernel\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;










class DebugHandlersListener implements EventSubscriberInterface
{
private string|object|null $earlyHandler;
private ?\Closure $exceptionHandler;
private bool $webMode;
private bool $firstCall = true;
private bool $hasTerminatedWithException = false;





public function __construct(?callable $exceptionHandler = null, bool|LoggerInterface|null $webMode = null)
{
if ($webMode instanceof LoggerInterface) {

$webMode = null;
}
$handler = set_exception_handler('is_int');
$this->earlyHandler = \is_array($handler) ? $handler[0] : null;
restore_exception_handler();

$this->exceptionHandler = null === $exceptionHandler ? null : $exceptionHandler(...);
$this->webMode = $webMode ?? !\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true);
}




public function configure(?object $event = null): void
{
if ($event instanceof ConsoleEvent && $this->webMode) {
return;
}
if (!$event instanceof KernelEvent ? !$this->firstCall : !$event->isMainRequest()) {
return;
}
$this->firstCall = $this->hasTerminatedWithException = false;
$hasRun = null;

if (!$this->exceptionHandler) {
if ($event instanceof KernelEvent) {
if (method_exists($kernel = $event->getKernel(), 'terminateWithException')) {
$request = $event->getRequest();
$hasRun = &$this->hasTerminatedWithException;
$this->exceptionHandler = static function (\Throwable $e) use ($kernel, $request, &$hasRun) {
if ($hasRun) {
throw $e;
}

$hasRun = true;
$kernel->terminateWithException($e, $request);
};
}
} elseif ($event instanceof ConsoleEvent && $app = $event->getCommand()->getApplication()) {
$output = $event->getOutput();
if ($output instanceof ConsoleOutputInterface) {
$output = $output->getErrorOutput();
}
$this->exceptionHandler = static function (\Throwable $e) use ($app, $output) {
$app->renderThrowable($e, $output);
};
}
}
if ($this->exceptionHandler) {
$handler = set_exception_handler(static fn () => null);
$handler = \is_array($handler) ? $handler[0] : null;
restore_exception_handler();

if (!$handler instanceof ErrorHandler) {
$handler = $this->earlyHandler;
}

if ($handler instanceof ErrorHandler) {
$handler->setExceptionHandler($this->exceptionHandler);
if (null !== $hasRun) {
$throwAt = $handler->throwAt(0) | \E_ERROR | \E_CORE_ERROR | \E_COMPILE_ERROR | \E_USER_ERROR | \E_RECOVERABLE_ERROR | \E_PARSE;
$loggers = [];

foreach ($handler->setLoggers([]) as $type => $log) {
if ($type & $throwAt) {
$loggers[$type] = [null, $log[1]];
}
}


$handler->setLoggers($loggers);
}
}
$this->exceptionHandler = null;
}
}

public static function getSubscribedEvents(): array
{
$events = [KernelEvents::REQUEST => ['configure', 2048]];

if (\defined('Symfony\Component\Console\ConsoleEvents::COMMAND')) {
$events[ConsoleEvents::COMMAND] = ['configure', 2048];
}

return $events;
}
}
