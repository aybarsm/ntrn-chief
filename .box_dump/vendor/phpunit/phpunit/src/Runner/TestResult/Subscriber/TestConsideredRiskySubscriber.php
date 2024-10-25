<?php declare(strict_types=1);








namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\ConsideredRiskySubscriber;

/**
@no-named-arguments


*/
final class TestConsideredRiskySubscriber extends Subscriber implements ConsideredRiskySubscriber
{
public function notify(ConsideredRisky $event): void
{
$this->collector()->testConsideredRisky($event);
}
}
