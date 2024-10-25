<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestSize;

/**
@no-named-arguments
@psalm-immutable



*/
final class Medium extends Known
{
/**
@psalm-assert-if-true
*/
public function isMedium(): bool
{
return true;
}

public function isGreaterThan(TestSize $other): bool
{
return $other->isSmall();
}

public function asString(): string
{
return 'medium';
}
}
