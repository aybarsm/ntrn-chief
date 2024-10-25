<?php declare(strict_types=1);








namespace PHPUnit\Runner\ResultCache;

use function round;
use PHPUnit\Event\Event;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\TestStatus\TestStatus;

/**
@no-named-arguments


*/
final class ResultCacheHandler
{
private readonly ResultCache $cache;
private ?HRTime $time = null;
private int $testSuite = 0;





public function __construct(ResultCache $cache, Facade $facade)
{
$this->cache = $cache;

$this->registerSubscribers($facade);
}

public function testSuiteStarted(): void
{
$this->testSuite++;
}

public function testSuiteFinished(): void
{
$this->testSuite--;

if ($this->testSuite === 0) {
$this->cache->persist();
}
}

public function testPrepared(Prepared $event): void
{
$this->time = $event->telemetryInfo()->time();
}

public function testMarkedIncomplete(MarkedIncomplete $event): void
{
$this->cache->setStatus(
$event->test()->id(),
TestStatus::incomplete($event->throwable()->message()),
);
}

public function testConsideredRisky(ConsideredRisky $event): void
{
$this->cache->setStatus(
$event->test()->id(),
TestStatus::risky($event->message()),
);
}

public function testErrored(Errored $event): void
{
$this->cache->setStatus(
$event->test()->id(),
TestStatus::error($event->throwable()->message()),
);
}

public function testFailed(Failed $event): void
{
$this->cache->setStatus(
$event->test()->id(),
TestStatus::failure($event->throwable()->message()),
);
}





public function testSkipped(Skipped $event): void
{
$this->cache->setStatus(
$event->test()->id(),
TestStatus::skipped($event->message()),
);

$this->cache->setTime($event->test()->id(), $this->duration($event));
}





public function testFinished(Finished $event): void
{
$this->cache->setTime($event->test()->id(), $this->duration($event));

$this->time = null;
}





private function duration(Event $event): float
{
if ($this->time === null) {
return 0.0;
}

return round($event->telemetryInfo()->time()->duration($this->time)->asFloat(), 3);
}





private function registerSubscribers(Facade $facade): void
{
$facade->registerSubscribers(
new TestSuiteStartedSubscriber($this),
new TestSuiteFinishedSubscriber($this),
new TestPreparedSubscriber($this),
new TestMarkedIncompleteSubscriber($this),
new TestConsideredRiskySubscriber($this),
new TestErroredSubscriber($this),
new TestFailedSubscriber($this),
new TestSkippedSubscriber($this),
new TestFinishedSubscriber($this),
);
}
}