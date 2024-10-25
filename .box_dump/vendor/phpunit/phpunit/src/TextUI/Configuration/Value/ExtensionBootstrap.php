<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

/**
@no-named-arguments
@psalm-immutable

*/
final class ExtensionBootstrap
{
/**
@psalm-var
*/
private readonly string $className;

/**
@psalm-var
*/
private readonly array $parameters;

/**
@psalm-param
@psalm-param
*/
public function __construct(string $className, array $parameters)
{
$this->className = $className;
$this->parameters = $parameters;
}

/**
@psalm-return
*/
public function className(): string
{
return $this->className;
}

/**
@psalm-return
*/
public function parameters(): array
{
return $this->parameters;
}
}
