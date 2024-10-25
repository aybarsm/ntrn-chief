<?php declare(strict_types=1);








namespace PHPUnit\Event\Test;

use function sprintf;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry\Info;

/**
@psalm-immutable
@no-named-arguments

*/
final class DataProviderMethodCalled implements Event
{
private readonly Info $telemetryInfo;
private readonly ClassMethod $testMethod;
private readonly ClassMethod $dataProviderMethod;

public function __construct(Info $telemetryInfo, ClassMethod $testMethod, ClassMethod $dataProviderMethod)
{
$this->telemetryInfo = $telemetryInfo;
$this->testMethod = $testMethod;
$this->dataProviderMethod = $dataProviderMethod;
}

public function telemetryInfo(): Info
{
return $this->telemetryInfo;
}

public function testMethod(): ClassMethod
{
return $this->testMethod;
}

public function dataProviderMethod(): ClassMethod
{
return $this->dataProviderMethod;
}

public function asString(): string
{
return sprintf(
'Data Provider Method Called (%s::%s for test method %s::%s)',
$this->dataProviderMethod->className(),
$this->dataProviderMethod->methodName(),
$this->testMethod->className(),
$this->testMethod->methodName(),
);
}
}
