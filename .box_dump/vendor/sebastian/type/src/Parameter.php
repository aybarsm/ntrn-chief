<?php declare(strict_types=1);








namespace SebastianBergmann\Type;

final class Parameter
{
/**
@psalm-var
*/
private string $name;
private Type $type;

/**
@psalm-param
*/
public function __construct(string $name, Type $type)
{
$this->name = $name;
$this->type = $type;
}

public function name(): string
{
return $this->name;
}

public function type(): Type
{
return $this->type;
}
}
