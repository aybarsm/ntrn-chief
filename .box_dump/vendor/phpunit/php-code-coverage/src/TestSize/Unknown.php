<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\Test\TestSize;

/**
@psalm-immutable
*/
final class Unknown extends TestSize
{
/**
@psalm-assert-if-true
*/
public function isUnknown(): bool
{
return true;
}

public function asString(): string
{
return 'unknown';
}
}
