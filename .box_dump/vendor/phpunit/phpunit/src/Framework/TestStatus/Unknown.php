<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
final class Unknown extends TestStatus
{
/**
@psalm-assert-if-true
*/
public function isUnknown(): bool
{
return true;
}

public function asInt(): int
{
return -1;
}

public function asString(): string
{
return 'unknown';
}
}
