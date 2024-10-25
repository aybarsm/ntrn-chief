<?php declare(strict_types=1);








namespace PHPUnit\Event\Application;

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
private readonly int $shellExitCode;

public function __construct(Telemetry\Info $telemetryInfo, int $shellExitCode)
{
$this->telemetryInfo = $telemetryInfo;
$this->shellExitCode = $shellExitCode;
}

public function telemetryInfo(): Telemetry\Info
{
return $this->telemetryInfo;
}

public function shellExitCode(): int
{
return $this->shellExitCode;
}

public function asString(): string
{
return sprintf(
'PHPUnit Finished (Shell Exit Code: %d)',
$this->shellExitCode,
);
}
}
