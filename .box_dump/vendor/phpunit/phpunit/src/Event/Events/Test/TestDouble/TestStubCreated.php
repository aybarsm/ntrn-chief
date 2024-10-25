<?php declare(strict_types=1);








namespace PHPUnit\Event\Test;

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class TestStubCreated implements Event
{
private readonly Telemetry\Info $telemetryInfo;




private readonly string $className;

/**
@psalm-param
*/
public function __construct(Telemetry\Info $telemetryInfo, string $className)
{
$this->telemetryInfo = $telemetryInfo;
$this->className = $className;
}

public function telemetryInfo(): Telemetry\Info
{
return $this->telemetryInfo;
}




public function className(): string
{
return $this->className;
}

public function asString(): string
{
return sprintf(
'Test Stub Created (%s)',
$this->className,
);
}
}
