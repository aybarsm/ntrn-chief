<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class RunInSeparateProcess extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isRunInSeparateProcess(): bool
{
return true;
}
}
