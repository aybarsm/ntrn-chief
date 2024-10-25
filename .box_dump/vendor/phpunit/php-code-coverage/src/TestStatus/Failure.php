<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\Test\TestStatus;

/**
@psalm-immutable
*/
final class Failure extends Known
{
/**
@psalm-assert-if-true
*/
public function isFailure(): bool
{
return true;
}

public function asString(): string
{
return 'failure';
}
}
