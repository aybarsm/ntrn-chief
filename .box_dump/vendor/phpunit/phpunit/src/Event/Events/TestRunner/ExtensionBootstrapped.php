<?php declare(strict_types=1);








namespace PHPUnit\Event\TestRunner;

use function sprintf;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class ExtensionBootstrapped implements Event
{
private readonly Telemetry\Info $telemetryInfo;

/**
@psalm-var
*/
private readonly string $className;

/**
@psalm-var
*/
private readonly array $parameters;

/**
@psalm-param
@psalm-param
*/
public function __construct(Telemetry\Info $telemetryInfo, string $className, array $parameters)
{
$this->telemetryInfo = $telemetryInfo;
$this->className = $className;
$this->parameters = $parameters;
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
public function parameters(): array
{
return $this->parameters;
}

public function asString(): string
{
return sprintf(
'Extension Bootstrapped (%s)',
$this->className,
);
}
}
