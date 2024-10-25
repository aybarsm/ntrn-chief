<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\Test\TestStatus;

/**
@psalm-immutable
*/
abstract class Known extends TestStatus
{
/**
@psalm-assert-if-true
*/
public function isKnown(): bool
{
return true;
}
}
