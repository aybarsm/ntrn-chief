<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class CoversNothing extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isCoversNothing(): bool
{
return true;
}
}
