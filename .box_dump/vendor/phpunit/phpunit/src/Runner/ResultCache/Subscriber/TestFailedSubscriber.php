<?php declare(strict_types=1);








namespace PHPUnit\Runner\ResultCache;

use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;

/**
@no-named-arguments


*/
final class TestFailedSubscriber extends Subscriber implements FailedSubscriber
{
public function notify(Failed $event): void
{
$this->handler()->testFailed($event);
}
}
