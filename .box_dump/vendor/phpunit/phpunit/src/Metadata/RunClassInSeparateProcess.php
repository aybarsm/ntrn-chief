<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class RunClassInSeparateProcess extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isRunClassInSeparateProcess(): bool
{
return true;
}
}
