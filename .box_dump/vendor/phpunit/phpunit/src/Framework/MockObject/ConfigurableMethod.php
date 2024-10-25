<?php declare(strict_types=1);








namespace PHPUnit\Framework\MockObject;

use SebastianBergmann\Type\Type;

/**
@no-named-arguments


*/
final class ConfigurableMethod
{
/**
@psalm-var
*/
private readonly string $name;

/**
@psalm-var
*/
private readonly array $defaultParameterValues;

/**
@psalm-var
*/
private readonly int $numberOfParameters;
private readonly Type $returnType;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function __construct(string $name, array $defaultParameterValues, int $numberOfParameters, Type $returnType)
{
$this->name = $name;
$this->defaultParameterValues = $defaultParameterValues;
$this->numberOfParameters = $numberOfParameters;
$this->returnType = $returnType;
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
public function defaultParameterValues(): array
{
return $this->defaultParameterValues;
}

/**
@psalm-return
*/
public function numberOfParameters(): int
{
return $this->numberOfParameters;
}

public function mayReturn(mixed $value): bool
{
return $this->returnType->isAssignable(Type::fromValue($value, false));
}

public function returnTypeDeclaration(): string
{
return $this->returnType->asString();
}
}
