<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
final class Warning extends Known
{
/**
@psalm-assert-if-true
*/
public function isWarning(): bool
{
return true;
}

public function asInt(): int
{
return 6;
}

public function asString(): string
{
return 'warning';
}
}
