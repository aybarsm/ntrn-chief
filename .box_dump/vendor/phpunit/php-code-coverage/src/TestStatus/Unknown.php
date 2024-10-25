<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\Test\TestStatus;

/**
@psalm-immutable
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

public function asString(): string
{
return 'unknown';
}
}
