<?php declare(strict_types=1);








namespace PHPUnit\Event\Test;

use function sprintf;
use PHPUnit\Event\Code;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class PreparationStarted implements Event
{
private readonly Telemetry\Info $telemetryInfo;
private readonly Code\Test $test;

public function __construct(Telemetry\Info $telemetryInfo, Code\Test $test)
{
$this->telemetryInfo = $telemetryInfo;
$this->test = $test;
}

public function telemetryInfo(): Telemetry\Info
{
return $this->telemetryInfo;
}

public function test(): Code\Test
{
return $this->test;
}

public function asString(): string
{
return sprintf(
'Test Preparation Started (%s)',
$this->test->id(),
);
}
}
