<?php declare(strict_types=1);








namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\Code\TestCollection;

/**
@psalm-immutable
@no-named-arguments

*/
final class TestSuiteForTestClass extends TestSuite
{
/**
@psalm-var
*/
private readonly string $className;
private readonly string $file;
private readonly int $line;

/**
@psalm-param
*/
public function __construct(string $name, int $size, TestCollection $tests, string $file, int $line)
{
parent::__construct($name, $size, $tests);

$this->className = $name;
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
public function isForTestClass(): bool
{
return true;
}
}
