<?php declare(strict_types=1);








namespace PHPUnit\Runner\ResultCache;

use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;

/**
@no-named-arguments


*/
final class TestSuiteStartedSubscriber extends Subscriber implements StartedSubscriber
{
public function notify(Started $event): void
{
$this->handler()->testSuiteStarted();
}
}