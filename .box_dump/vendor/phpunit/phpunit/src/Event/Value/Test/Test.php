<?php declare(strict_types=1);








namespace PHPUnit\Event\Code;

/**
@psalm-immutable
@no-named-arguments

*/
abstract class Test
{
/**
@psalm-var
*/
private readonly string $file;

/**
@psalm-param
*/
public function __construct(string $file)
{
$this->file = $file;
}

/**
@psalm-return
*/
public function file(): string
{
return $this->file;
}

/**
@psalm-assert-if-true
*/
public function isTestMethod(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isPhpt(): bool
{
return false;
}

/**
@psalm-return
*/
abstract public function id(): string;

/**
@psalm-return
*/
abstract public function name(): string;
}
