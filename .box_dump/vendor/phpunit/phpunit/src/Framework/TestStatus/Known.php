<?php declare(strict_types=1);








namespace PHPUnit\Framework\TestStatus;

/**
@psalm-immutable
@no-named-arguments



*/
abstract class Known extends TestStatus
{
/**
@psalm-assert-if-true
*/
public function isKnown(): bool
{
return true;
}
}
