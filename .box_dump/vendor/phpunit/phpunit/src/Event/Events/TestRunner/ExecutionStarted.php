<?php declare(strict_types=1);








namespace PHPUnit\Event\TestRunner;

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;
use PHPUnit\Event\TestSuite\TestSuite;

/**
@psalm-immutable
@no-named-arguments

*/
final class ExecutionStarted implements Event
{
private readonly Telemetry\Info $telemetryInfo;
private readonly TestSuite $testSuite;

public function __construct(Telemetry\Info $telemetryInfo, TestSuite $testSuite)
{
$this->telemetryInfo = $telemetryInfo;
$this->testSuite = $testSuite;
}

public function telemetryInfo(): Telemetry\Info
{
return $this->telemetryInfo;
}

public function testSuite(): TestSuite
{
return $this->testSuite;
}

public function asString(): string
{
return sprintf(
'Test Runner Execution Started (%d test%s)',
$this->testSuite->count(),
$this->testSuite->count() !== 1 ? 's' : '',
);
}
}
