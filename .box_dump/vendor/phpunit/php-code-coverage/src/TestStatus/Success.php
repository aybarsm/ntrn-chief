<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\Test\TestStatus;

/**
@psalm-immutable
*/
final class Success extends Known
{
/**
@psalm-assert-if-true
*/
public function isSuccess(): bool
{
return true;
}

public function asString(): string
{
return 'success';
}
}
