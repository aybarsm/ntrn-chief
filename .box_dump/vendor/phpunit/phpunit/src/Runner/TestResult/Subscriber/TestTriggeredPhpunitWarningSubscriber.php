<?php declare(strict_types=1);








namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggeredSubscriber;

/**
@no-named-arguments


*/
final class TestTriggeredPhpunitWarningSubscriber extends Subscriber implements PhpunitWarningTriggeredSubscriber
{
public function notify(PhpunitWarningTriggered $event): void
{
$this->collector()->testTriggeredPhpunitWarning($event);
}
}
