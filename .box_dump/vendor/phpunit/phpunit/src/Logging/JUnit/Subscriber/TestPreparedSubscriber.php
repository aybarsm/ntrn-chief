<?php declare(strict_types=1);








namespace PHPUnit\Logging\JUnit;

use PHPUnit\Event\InvalidArgumentException;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;

/**
@no-named-arguments


*/
final class TestPreparedSubscriber extends Subscriber implements PreparedSubscriber
{



public function notify(Prepared $event): void
{
$this->logger()->testPrepared();
}
}
