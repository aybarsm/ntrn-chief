<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class AfterClass extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isAfterClass(): bool
{
return true;
}
}
