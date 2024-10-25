<?php declare(strict_types=1);








namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\Test\WarningTriggeredSubscriber;

/**
@no-named-arguments


*/
final class TestTriggeredWarningSubscriber extends Subscriber implements WarningTriggeredSubscriber
{
public function notify(WarningTriggered $event): void
{
$this->collector()->testTriggeredWarning($event);
}
}
