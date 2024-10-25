<?php declare(strict_types=1);








namespace PHPUnit\Event\TestSuite;

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class Finished implements Event
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
'Test Suite Finished (%s, %d test%s)',
$this->testSuite->name(),
$this->testSuite->count(),
$this->testSuite->count() !== 1 ? 's' : '',
);
}
}
