<?php declare(strict_types=1);








namespace PHPUnit\Event\Code;

/**
@psalm-immutable
@no-named-arguments

*/
final class Phpt extends Test
{
/**
@psalm-assert-if-true
*/
public function isPhpt(): bool
{
return true;
}

/**
@psalm-return
*/
public function id(): string
{
return $this->file();
}

/**
@psalm-return
*/
public function name(): string
{
return $this->file();
}
}
