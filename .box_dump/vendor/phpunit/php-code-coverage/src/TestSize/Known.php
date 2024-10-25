<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\Test\TestSize;

/**
@psalm-immutable
*/
abstract class Known extends TestSize
{
/**
@psalm-assert-if-true
*/
public function isKnown(): bool
{
return true;
}

abstract public function isGreaterThan(self $other): bool;
}
