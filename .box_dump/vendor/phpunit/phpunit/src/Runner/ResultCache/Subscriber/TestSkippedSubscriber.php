<?php declare(strict_types=1);








namespace PHPUnit\Runner\ResultCache;

use PHPUnit\Event\InvalidArgumentException;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;

/**
@no-named-arguments


*/
final class TestSkippedSubscriber extends Subscriber implements SkippedSubscriber
{




public function notify(Skipped $event): void
{
$this->handler()->testSkipped($event);
}
}
