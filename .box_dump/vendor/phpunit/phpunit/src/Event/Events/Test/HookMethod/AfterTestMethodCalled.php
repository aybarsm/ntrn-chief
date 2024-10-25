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
final class AfterTestMethodCalled implements Event
{
private readonly Telemetry\Info $telemetryInfo;

/**
@psalm-var
*/
private readonly string $testClassName;
private readonly Code\ClassMethod $calledMethod;

/**
@psalm-param
*/
public function __construct(Telemetry\Info $telemetryInfo, string $testClassName, Code\ClassMethod $calledMethod)
{
$this->telemetryInfo = $telemetryInfo;
$this->testClassName = $testClassName;
$this->calledMethod = $calledMethod;
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

public function asString(): string
{
return sprintf(
'After Test Method Called (%s::%s)',
$this->calledMethod->className(),
$this->calledMethod->methodName(),
);
}
}