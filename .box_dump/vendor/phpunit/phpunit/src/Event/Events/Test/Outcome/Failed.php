<?php declare(strict_types=1);








namespace PHPUnit\Event\Test;

use const PHP_EOL;
use function sprintf;
use function trim;
use PHPUnit\Event\Code;
use PHPUnit\Event\Code\ComparisonFailure;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class Failed implements Event
{
private readonly Telemetry\Info $telemetryInfo;
private readonly Code\Test $test;
private readonly Throwable $throwable;
private readonly ?ComparisonFailure $comparisonFailure;

public function __construct(Telemetry\Info $telemetryInfo, Code\Test $test, Throwable $throwable, ?ComparisonFailure $comparisonFailure)
{
$this->telemetryInfo = $telemetryInfo;
$this->test = $test;
$this->throwable = $throwable;
$this->comparisonFailure = $comparisonFailure;
}

public function telemetryInfo(): Telemetry\Info
{
return $this->telemetryInfo;
}

public function test(): Code\Test
{
return $this->test;
}

public function throwable(): Throwable
{
return $this->throwable;
}

/**
@psalm-assert-if-true
*/
public function hasComparisonFailure(): bool
{
return $this->comparisonFailure !== null;
}




public function comparisonFailure(): ComparisonFailure
{
if ($this->comparisonFailure === null) {
throw new NoComparisonFailureException;
}

return $this->comparisonFailure;
}

public function asString(): string
{
$message = trim($this->throwable->message());

if (!empty($message)) {
$message = PHP_EOL . $message;
}

return sprintf(
'Test Failed (%s)%s',
$this->test->id(),
$message,
);
}
}
