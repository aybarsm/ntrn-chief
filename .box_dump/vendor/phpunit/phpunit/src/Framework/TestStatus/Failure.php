<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
final class Failure extends Known
{
/**
@psalm-assert-if-true
*/
public function isFailure(): bool
{
return true;
}

public function asInt(): int
{
return 7;
}

public function asString(): string
{
return 'failure';
}
}
