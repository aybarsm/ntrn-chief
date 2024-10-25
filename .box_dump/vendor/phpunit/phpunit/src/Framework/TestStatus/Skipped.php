<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
final class Skipped extends Known
{
/**
@psalm-assert-if-true
*/
public function isSkipped(): bool
{
return true;
}

public function asInt(): int
{
return 1;
}

public function asString(): string
{
return 'skipped';
}
}
