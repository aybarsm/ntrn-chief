<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\Test\TestStatus;

/**
@psalm-immutable
*/
abstract class TestStatus
{
public static function unknown(): self
{
return new Unknown;
}

public static function success(): self
{
return new Success;
}

public static function failure(): self
{
return new Failure;
}

/**
@psalm-assert-if-true
*/
public function isKnown(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isUnknown(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isSuccess(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isFailure(): bool
{
return false;
}

abstract public function asString(): string;
}
