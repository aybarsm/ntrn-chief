<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\Test\TestSize;

/**
@psalm-immutable
*/
final class Large extends Known
{
/**
@psalm-assert-if-true
*/
public function isLarge(): bool
{
return true;
}

public function isGreaterThan(TestSize $other): bool
{
return !$other->isLarge();
}

public function asString(): string
{
return 'large';
}
}
