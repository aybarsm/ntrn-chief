<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class Test extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isTest(): bool
{
return true;
}
}
