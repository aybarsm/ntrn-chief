<?php declare(strict_types=1);








namespace PHPUnit\TestRunner\TestResult\Issues;

use PHPUnit\Event\Code\Test;

/**
@no-named-arguments


*/
final class Issue
{
/**
@psalm-var
*/
private readonly string $file;

/**
@psalm-var
*/
private readonly int $line;

/**
@psalm-var
*/
private readonly string $description;

/**
@psalm-var
*/
private array $triggeringTests;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public static function from(string $file, int $line, string $description, Test $triggeringTest): self
{
return new self($file, $line, $description, $triggeringTest);
}

/**
@psalm-param
@psalm-param
@psalm-param
*/
private function __construct(string $file, int $line, string $description, Test $triggeringTest)
{
$this->file = $file;
$this->line = $line;
$this->description = $description;

$this->triggeringTests = [
$triggeringTest->id() => [
'test' => $triggeringTest,
'count' => 1,
],
];
}

public function triggeredBy(Test $test): void
{
if (isset($this->triggeringTests[$test->id()])) {
$this->triggeringTests[$test->id()]['count']++;

return;
}

$this->triggeringTests[$test->id()] = [
'test' => $test,
'count' => 1,
];
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

/**
@psalm-return
*/
public function description(): string
{
return $this->description;
}

/**
@psalm-return
*/
public function triggeringTests(): array
{
return $this->triggeringTests;
}
}
