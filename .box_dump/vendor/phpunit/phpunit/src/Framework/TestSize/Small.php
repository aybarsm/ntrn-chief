<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestSize;

/**
@no-named-arguments
@psalm-immutable



*/
final class Small extends Known
{
/**
@psalm-assert-if-true
*/
public function isSmall(): bool
{
return true;
}

public function isGreaterThan(TestSize $other): bool
{
return false;
}

public function asString(): string
{
return 'small';
}
}
