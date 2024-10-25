<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

/**
@no-named-arguments
@psalm-immutable

*/
final class Directory
{
private readonly string $path;

public function __construct(string $path)
{
$this->path = $path;
}

public function path(): string
{
return $this->path;
}
}
