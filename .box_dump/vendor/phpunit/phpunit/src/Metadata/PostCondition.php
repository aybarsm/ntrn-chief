<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class PostCondition extends Metadata
{
/**
@psalm-assert-if-true
*/
public function isPostCondition(): bool
{
return true;
}
}
