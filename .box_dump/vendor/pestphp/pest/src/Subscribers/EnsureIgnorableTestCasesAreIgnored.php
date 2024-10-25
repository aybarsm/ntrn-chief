<?php

declare(strict_types=1);

namespace Pest\Subscribers;

use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\TestRunner\StartedSubscriber;
use PHPUnit\Event\TestRunner\WarningTriggered;
use PHPUnit\TestRunner\TestResult\Collector;
use PHPUnit\TestRunner\TestResult\Facade;
use ReflectionClass;




final class EnsureIgnorableTestCasesAreIgnored implements StartedSubscriber
{



public function notify(Started $event): void
{
$reflection = new ReflectionClass(Facade::class);
$property = $reflection->getProperty('collector');
$property->setAccessible(true);
$collector = $property->getValue();

assert($collector instanceof Collector);

$reflection = new ReflectionClass($collector);
$property = $reflection->getProperty('testRunnerTriggeredWarningEvents');
$property->setAccessible(true);


$testRunnerTriggeredWarningEvents = $property->getValue($collector);

$testRunnerTriggeredWarningEvents = array_values(array_filter($testRunnerTriggeredWarningEvents, fn (WarningTriggered $event): bool => $event->message() !== 'No tests found in class "Pest\TestCases\IgnorableTestCase".'));

$property->setValue($collector, $testRunnerTriggeredWarningEvents);
}
}
