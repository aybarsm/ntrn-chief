<?php declare(strict_types=1);








namespace PHPUnit\Event\Test;

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class PartialMockObjectCreated implements Event
{
private readonly Telemetry\Info $telemetryInfo;

/**
@psalm-var
*/
private readonly string $className;

/**
@psalm-var
*/
private readonly array $methodNames;

/**
@psalm-param
*/
public function __construct(Telemetry\Info $telemetryInfo, string $className, string ...$methodNames)
{
$this->telemetryInfo = $telemetryInfo;
$this->className = $className;
$this->methodNames = $methodNames;
}

public function telemetryInfo(): Telemetry\Info
{
return $this->telemetryInfo;
}

/**
@psalm-return
*/
public function className(): string
{
return $this->className;
}

/**
@psalm-return
*/
public function methodNames(): array
{
return $this->methodNames;
}

public function asString(): string
{
return sprintf(
'Partial Mock Object Created (%s)',
$this->className,
);
}
}
