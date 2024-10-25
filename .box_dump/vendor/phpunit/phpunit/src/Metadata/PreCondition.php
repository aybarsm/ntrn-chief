<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class PreCondition extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isPreCondition(): bool
{
return true;
}
}
