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
final class ErrorTriggered implements Event
{
private readonly Telemetry\Info $telemetryInfo;
private readonly Test $test;

/**
@psalm-var
*/
private readonly string $message;

/**
@psalm-var
*/
private readonly string $file;

/**
@psalm-var
*/
private readonly int $line;
private readonly bool $suppressed;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function __construct(Telemetry\Info $telemetryInfo, Test $test, string $message, string $file, int $line, bool $suppressed)
{
$this->telemetryInfo = $telemetryInfo;
$this->test = $test;
$this->message = $message;
$this->file = $file;
$this->line = $line;
$this->suppressed = $suppressed;
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

/**
@psalm-return
*/
public function file(): string
{
return $this->file;
}

/**
@psalm-return
*/
public function line(): int
{
return $this->line;
}

public function wasSuppressed(): bool
{
return $this->suppressed;
}

public function asString(): string
{
$message = $this->message;

if (!empty($message)) {
$message = PHP_EOL . $message;
}

return sprintf(
'Test Triggered %sError (%s)%s',
$this->wasSuppressed() ? 'Suppressed ' : '',
$this->test->id(),
$message,
);
}
}
