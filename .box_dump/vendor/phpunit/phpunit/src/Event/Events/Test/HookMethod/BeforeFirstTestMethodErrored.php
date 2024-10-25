<?php declare(strict_types=1);








namespace PHPUnit\Event\Test;

use const PHP_EOL;
use function sprintf;
use PHPUnit\Event\Code;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class BeforeFirstTestMethodErrored implements Event
{
private readonly Telemetry\Info $telemetryInfo;

/**
@psalm-var
*/
private readonly string $testClassName;
private readonly Code\ClassMethod $calledMethod;
private readonly Throwable $throwable;

/**
@psalm-param
*/
public function __construct(Telemetry\Info $telemetryInfo, string $testClassName, Code\ClassMethod $calledMethod, Throwable $throwable)
{
$this->telemetryInfo = $telemetryInfo;
$this->testClassName = $testClassName;
$this->calledMethod = $calledMethod;
$this->throwable = $throwable;
}

public function telemetryInfo(): Telemetry\Info
{
return $this->telemetryInfo;
}

/**
@psalm-return
*/
public function testClassName(): string
{
return $this->testClassName;
}

public function calledMethod(): Code\ClassMethod
{
return $this->calledMethod;
}

public function throwable(): Throwable
{
return $this->throwable;
}

public function asString(): string
{
$message = $this->throwable->message();

if (!empty($message)) {
$message = PHP_EOL . $message;
}

return sprintf(
'Before First Test Method Errored (%s::%s)%s',
$this->calledMethod->className(),
$this->calledMethod->methodName(),
$message,
);
}
}
