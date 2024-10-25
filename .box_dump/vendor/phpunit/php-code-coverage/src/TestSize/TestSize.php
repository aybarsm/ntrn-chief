<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\Test\TestSize;

/**
@psalm-immutable
*/
abstract class TestSize
{
public static function unknown(): self
{
return new Unknown;
}

public static function small(): self
{
return new Small;
}

public static function medium(): self
{
return new Medium;
}

public static function large(): self
{
return new Large;
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
public function isSmall(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isMedium(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isLarge(): bool
{
return false;
}

abstract public function asString(): string;
}
