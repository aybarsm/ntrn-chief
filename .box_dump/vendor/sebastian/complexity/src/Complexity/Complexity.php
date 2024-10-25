<?php declare(strict_types=1);








namespace SebastianBergmann\Complexity;

use function str_contains;

/**
@psalm-immutable
*/
final class Complexity
{
/**
@psalm-var
*/
private readonly string $name;

/**
@psalm-var
*/
private int $cyclomaticComplexity;

/**
@psalm-param
@psalm-param
*/
public function __construct(string $name, int $cyclomaticComplexity)
{
$this->name = $name;
$this->cyclomaticComplexity = $cyclomaticComplexity;
}

/**
@psalm-return
*/
public function name(): string
{
return $this->name;
}

/**
@psalm-return
*/
public function cyclomaticComplexity(): int
{
return $this->cyclomaticComplexity;
}

public function isFunction(): bool
{
return !$this->isMethod();
}

public function isMethod(): bool
{
return str_contains($this->name, '::');
}
}
