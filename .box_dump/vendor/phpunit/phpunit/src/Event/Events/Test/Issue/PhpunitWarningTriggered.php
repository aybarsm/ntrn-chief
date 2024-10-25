<?php declare(strict_types=1);








namespace PHPUnit\Event\Test;

use const PHP_EOL;
use function sprintf;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry;

/**
@psalm-immutable
@no-named-arguments

*/
final class PhpunitWarningTriggered implements Event
{
private readonly Telemetry\Info $telemetryInfo;
private readonly Test $test;

/**
@psalm-var
*/
private readonly string $message;

/**
@psalm-param
*/
public function __construct(Telemetry\Info $telemetryInfo, Test $test, string $message)
{
$this->telemetryInfo = $telemetryInfo;
$this->test = $test;
$this->message = $message;
}

public function telemetryInfo(): Telemetry\Info
{
return $this->telemetryInfo;
}

public function test(): Test
{
return $this->test;
}

/**
@psalm-return
*/
public function message(): string
{
return $this->message;
}

public function asString(): string
{
$message = $this->message;

if (!empty($message)) {
$message = PHP_EOL . $message;
}

return sprintf(
'Test Triggered PHPUnit Warning (%s)%s',
$this->test->id(),
$message,
);
}
}
