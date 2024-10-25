<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
final class Notice extends Known
{
/**
@psalm-assert-if-true
*/
public function isNotice(): bool
{
return true;
}

public function asInt(): int
{
return 3;
}

public function asString(): string
{
return 'notice';
}
}
