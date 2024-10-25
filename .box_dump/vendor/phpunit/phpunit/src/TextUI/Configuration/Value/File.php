<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

/**
@no-named-arguments
@psalm-immutable

*/
final class File
{
/**
@psalm-var
*/
private readonly string $path;

/**
@psalm-param
*/
public function __construct(string $path)
{
$this->path = $path;
}

/**
@psalm-return
*/
public function path(): string
{
return $this->path;
}
}
