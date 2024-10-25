<?php declare(strict_types=1);








namespace PHPUnit\Event\Application;

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Runtime\Runtime;
use PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class Started implements Event
{
private readonly Telemetry\Info $telemetryInfo;
private readonly Runtime $runtime;

public function __construct(Telemetry\Info $telemetryInfo, Runtime $runtime)
{
$this->telemetryInfo = $telemetryInfo;
$this->runtime = $runtime;
}

public function telemetryInfo(): Telemetry\Info
{
return $this->telemetryInfo;
}

public function runtime(): Runtime
{
return $this->runtime;
}

public function asString(): string
{
return sprintf(
'PHPUnit Started (%s)',
$this->runtime->asString(),
);
}
}