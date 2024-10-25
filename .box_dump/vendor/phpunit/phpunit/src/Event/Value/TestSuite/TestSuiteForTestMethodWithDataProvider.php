<?php declare(strict_types=1);








namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\Code\TestCollection;

/**
@psalm-immutable
@no-named-arguments

*/
final class TestSuiteForTestMethodWithDataProvider extends TestSuite
{
/**
@psalm-var
*/
private readonly string $className;

/**
@psalm-var
*/
private readonly string $methodName;
private readonly string $file;
private readonly int $line;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function __construct(string $name, int $size, TestCollection $tests, string $className, string $methodName, string $file, int $line)
{
parent::__construct($name, $size, $tests);

$this->className = $className;
$this->methodName = $methodName;
$this->file = $file;
$this->line = $line;
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
public function methodName(): string
{
return $this->methodName;
}

public function file(): string
{
return $this->file;
}

public function line(): int
{
return $this->line;
}

/**
@psalm-assert-if-true
*/
public function isForTestMethodWithDataProvider(): bool
{
return true;
}
}
