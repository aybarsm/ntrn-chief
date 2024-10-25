<?php declare(strict_types=1);








namespace PHPUnit\Event\Test;

use const PHP_EOL;
use function sprintf;
use PHPUnit\Event\Code;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class BeforeFirstTestMethodFinished implements Event
{
private readonly Telemetry\Info$telemetryInfo;

/**
@psalm-var
*/
private readonly string $testClassName;

/**
@psalm-var
*/
private readonly array $calledMethods;

/**
@psalm-param
*/
public function __construct(Telemetry\Info $telemetryInfo, string $testClassName, Code\ClassMethod ...$calledMethods)
{
$this->telemetryInfo = $telemetryInfo;
$this->testClassName = $testClassName;
$this->calledMethods = $calledMethods;
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

/**
@psalm-return
*/
public function calledMethods(): array
{
return $this->calledMethods;
}

public function asString(): string
{
$buffer = 'Before First Test Method Finished:';

foreach ($this->calledMethods as $calledMethod) {
$buffer .= sprintf(
PHP_EOL . '- %s::%s',
$calledMethod->className(),
$calledMethod->methodName(),
);
}

return $buffer;
}
}
