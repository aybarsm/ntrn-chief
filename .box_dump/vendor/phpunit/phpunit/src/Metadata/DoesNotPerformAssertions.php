<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class DoesNotPerformAssertions extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isDoesNotPerformAssertions(): bool
{
return true;
}
}
