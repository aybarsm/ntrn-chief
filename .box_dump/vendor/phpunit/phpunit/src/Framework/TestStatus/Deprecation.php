<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
final class Deprecation extends Known
{
/**
@psalm-assert-if-true
*/
public function isDeprecation(): bool
{
return true;
}

public function asInt(): int
{
return 4;
}

public function asString(): string
{
return 'deprecation';
}
}
