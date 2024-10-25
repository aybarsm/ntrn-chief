<?php










namespace Symfony\Component\HttpKernel\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;






class TimeDataCollector extends DataCollector implements LateDataCollectorInterface
{
public function __construct(
private readonly ?KernelInterface $kernel = null,
private readonly ?Stopwatch $stopwatch = null,
) {
$this->data = ['events' => [], 'stopwatch_installed' => false, 'start_time' => 0];
}

public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
{
if (null !== $this->kernel) {
$startTime = $this->kernel->getStartTime();
} else {
$startTime = $request->server->get('REQUEST_TIME_FLOAT');
}

$this->data = [
'token' => $request->attributes->get('_stopwatch_token'),
'start_time' => $startTime * 1000,
'events' => [],
'stopwatch_installed' => class_exists(Stopwatch::class, false),
];
}

public function reset(): void
{
$this->data = ['events' => [], 'stopwatch_installed' => false, 'start_time' => 0];

$this->stopwatch?->reset();
}

public function lateCollect(): void
{
if (null !== $this->stopwatch && isset($this->data['token'])) {
$this->setEvents($this->stopwatch->getSectionEvents($this->data['token']));
}
unset($this->data['token']);
}




public function setEvents(array $events): void
{
foreach ($events as $event) {
$event->ensureStopped();
}

$this->data['events'] = $events;
}




public function getEvents(): array
{
return $this->data['events'];
}




public function getDuration(): float
{
if (!isset($this->data['events']['__section__'])) {
return 0;
}

$lastEvent = $this->data['events']['__section__'];

return $lastEvent->getOrigin() + $lastEvent->getDuration() - $this->getStartTime();
}






public function getInitTime(): float
{
if (!isset($this->data['events']['__section__'])) {
return 0;
}

return $this->data['events']['__section__']->getOrigin() - $this->getStartTime();
}

public function getStartTime(): float
{
return $this->data['start_time'];
}

public function isStopwatchInstalled(): bool
{
return $this->data['stopwatch_installed'];
}

public function getName(): string
{
return 'time';
}
}
