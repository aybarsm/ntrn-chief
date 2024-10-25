<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class Before extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isBefore(): bool
{
return true;
}
}
