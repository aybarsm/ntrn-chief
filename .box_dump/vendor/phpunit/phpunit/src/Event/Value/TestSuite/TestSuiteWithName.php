<?php declare(strict_types=1);








namespace PHPUnit\Event\TestSuite;

/**
@psalm-immutable
@no-named-arguments

*/
final class TestSuiteWithName extends TestSuite
{
/**
@psalm-assert-if-true
*/
public function isWithName(): bool
{
return true;
}
}
