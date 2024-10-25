<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
final class Success extends Known
{
/**
@psalm-assert-if-true
*/
public function isSuccess(): bool
{
return true;
}

public function asInt(): int
{
return 0;
}

public function asString(): string
{
return 'success';
}
}
