<?php declare(strict_types=1);








namespace PHPUnit\Runner\ResultCache;

use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\ConsideredRiskySubscriber;

/**
@no-named-arguments


*/
final class TestConsideredRiskySubscriber extends Subscriber implements ConsideredRiskySubscriber
{
public function notify(ConsideredRisky $event): void
{
$this->handler()->testConsideredRisky($event);
}
}
