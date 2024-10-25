<?php declare(strict_types=1);








namespace PHPUnit\TestRunner\TestResult;

use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggeredSubscriber;

/**
@no-named-arguments


*/
final class TestTriggeredPhpunitErrorSubscriber extends Subscriber implements PhpunitErrorTriggeredSubscriber
{
public function notify(PhpunitErrorTriggered $event): void
{
$this->collector()->testTriggeredPhpunitError($event);
}
}
