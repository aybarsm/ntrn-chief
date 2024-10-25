<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
final class Risky extends Known
{
/**
@psalm-assert-if-true
*/
public function isRisky(): bool
{
return true;
}

public function asInt(): int
{
return 5;
}

public function asString(): string
{
return 'risky';
}
}
