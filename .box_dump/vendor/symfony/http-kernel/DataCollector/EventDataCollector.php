<?php










namespace Symfony\Component\HttpKernel\DataCollector;

use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;








class EventDataCollector extends DataCollector implements LateDataCollectorInterface
{

private iterable $dispatchers;
private ?Request $currentRequest = null;




public function __construct(
iterable|EventDispatcherInterface|null $dispatchers = null,
private ?RequestStack $requestStack = null,
private string $defaultDispatcher = 'event_dispatcher',
) {
if ($dispatchers instanceof EventDispatcherInterface) {
$dispatchers = [$this->defaultDispatcher => $dispatchers];
}
$this->dispatchers = $dispatchers ?? [];
}

public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
{
$this->currentRequest = $this->requestStack && $this->requestStack->getMainRequest() !== $request ? $request : null;
$this->data = [];
}

public function reset(): void
{
parent::reset();

foreach ($this->dispatchers as $dispatcher) {
if ($dispatcher instanceof ResetInterface) {
$dispatcher->reset();
}
}
}

public function lateCollect(): void
{
foreach ($this->dispatchers as $name => $dispatcher) {
if (!$dispatcher instanceof TraceableEventDispatcher) {
continue;
}

$this->setCalledListeners($dispatcher->getCalledListeners($this->currentRequest), $name);
$this->setNotCalledListeners($dispatcher->getNotCalledListeners($this->currentRequest), $name);
$this->setOrphanedEvents($dispatcher->getOrphanedEvents($this->currentRequest), $name);
}

$this->data = $this->cloneVar($this->data);
}

public function getData(): array|Data
{
return $this->data;
}




public function setCalledListeners(array $listeners, ?string $dispatcher = null): void
{
$this->data[$dispatcher ?? $this->defaultDispatcher]['called_listeners'] = $listeners;
}




public function getCalledListeners(?string $dispatcher = null): array|Data
{
return $this->data[$dispatcher ?? $this->defaultDispatcher]['called_listeners'] ?? [];
}




public function setNotCalledListeners(array $listeners, ?string $dispatcher = null): void
{
$this->data[$dispatcher ?? $this->defaultDispatcher]['not_called_listeners'] = $listeners;
}




public function getNotCalledListeners(?string $dispatcher = null): array|Data
{
return $this->data[$dispatcher ?? $this->defaultDispatcher]['not_called_listeners'] ?? [];
}






public function setOrphanedEvents(array $events, ?string $dispatcher = null): void
{
$this->data[$dispatcher ?? $this->defaultDispatcher]['orphaned_events'] = $events;
}




public function getOrphanedEvents(?string $dispatcher = null): array|Data
{
return $this->data[$dispatcher ?? $this->defaultDispatcher]['orphaned_events'] ?? [];
}

public function getName(): string
{
return 'events';
}
}
