<?php declare(strict_types=1);








namespace PHPUnit\Logging\TestDox;

use PHPUnit\Event\InvalidArgumentException;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

/**
@no-named-arguments


*/
final class TestFinishedSubscriber extends Subscriber implements FinishedSubscriber
{



public function notify(Finished $event): void
{
$this->collector()->testFinished($event);
}
}