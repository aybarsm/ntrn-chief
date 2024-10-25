<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class IgnoreDeprecations extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isIgnoreDeprecations(): bool
{
return true;
}
}
