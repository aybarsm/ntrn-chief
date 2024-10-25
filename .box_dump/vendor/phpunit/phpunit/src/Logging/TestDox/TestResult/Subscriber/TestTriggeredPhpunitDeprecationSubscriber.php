<?php declare(strict_types=1);








namespace PHPUnit\Logging\TestDox;

use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggeredSubscriber;

/**
@no-named-arguments


*/
final class TestTriggeredPhpunitDeprecationSubscriber extends Subscriber implements PhpunitDeprecationTriggeredSubscriber
{
public function notify(PhpunitDeprecationTriggered $event): void
{
$this->collector()->testTriggeredPhpunitDeprecation($event);
}
}
