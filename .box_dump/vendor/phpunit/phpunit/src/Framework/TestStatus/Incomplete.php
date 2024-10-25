<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
final class Incomplete extends Known
{
/**
@psalm-assert-if-true
*/
public function isIncomplete(): bool
{
return true;
}

public function asInt(): int
{
return 2;
}

public function asString(): string
{
return 'incomplete';
}
}
