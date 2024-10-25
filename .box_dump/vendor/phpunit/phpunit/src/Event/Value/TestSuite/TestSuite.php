<?php declare(strict_types=1);








namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\Code\TestCollection;

/**
@psalm-immutable
@no-named-arguments

*/
abstract class TestSuite
{
/**
@psalm-var
*/
private readonly string $name;
private readonly int $count;
private readonly TestCollection $tests;

/**
@psalm-param
*/
public function __construct(string $name, int $size, TestCollection $tests)
{
$this->name = $name;
$this->count = $size;
$this->tests = $tests;
}

/**
@psalm-return
*/
public function name(): string
{
return $this->name;
}

public function count(): int
{
return $this->count;
}

public function tests(): TestCollection
{
return $this->tests;
}

/**
@psalm-assert-if-true
*/
public function isWithName(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isForTestClass(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isForTestMethodWithDataProvider(): bool
{
return false;
}
}
