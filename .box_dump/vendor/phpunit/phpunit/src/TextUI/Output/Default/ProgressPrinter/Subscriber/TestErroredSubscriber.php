<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Output\Default\ProgressPrinter;

use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;

/**
@no-named-arguments


*/
final class TestErroredSubscriber extends Subscriber implements ErroredSubscriber
{
public function notify(Errored $event): void
{
$this->printer()->testErrored($event);
}
}
