<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
final class Error extends Known
{
/**
@psalm-assert-if-true
*/
public function isError(): bool
{
return true;
}

public function asInt(): int
{
return 8;
}

public function asString(): string
{
return 'error';
}
}
