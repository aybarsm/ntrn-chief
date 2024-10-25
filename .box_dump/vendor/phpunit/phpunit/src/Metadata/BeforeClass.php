<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class BeforeClass extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isBeforeClass(): bool
{
return true;
}
}
