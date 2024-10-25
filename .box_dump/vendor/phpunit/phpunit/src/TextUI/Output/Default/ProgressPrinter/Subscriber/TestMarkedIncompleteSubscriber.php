<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Output\Default\ProgressPrinter;

use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\MarkedIncompleteSubscriber;

/**
@no-named-arguments


*/
final class TestMarkedIncompleteSubscriber extends Subscriber implements MarkedIncompleteSubscriber
{
public function notify(MarkedIncomplete $event): void
{
$this->printer()->testMarkedIncomplete();
}
}
