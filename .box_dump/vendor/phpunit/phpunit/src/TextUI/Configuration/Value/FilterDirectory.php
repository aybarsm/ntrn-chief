<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

/**
@no-named-arguments
@psalm-immutable

*/
final class FilterDirectory
{
/**
@psalm-var
*/
private readonly string $path;
private readonly string $prefix;
private readonly string $suffix;

/**
@psalm-param
*/
public function __construct(string $path, string $prefix, string $suffix)
{
$this->path = $path;
$this->prefix = $prefix;
$this->suffix = $suffix;
}

/**
@psalm-return
*/
public function path(): string
{
return $this->path;
}

public function prefix(): string
{
return $this->prefix;
}

public function suffix(): string
{
return $this->suffix;
}
}
