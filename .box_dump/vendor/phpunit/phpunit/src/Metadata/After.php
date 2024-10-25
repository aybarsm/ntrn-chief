<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class After extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isAfter(): bool
{
return true;
}
}
