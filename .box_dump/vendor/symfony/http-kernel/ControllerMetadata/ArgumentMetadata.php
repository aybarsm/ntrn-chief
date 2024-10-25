<?php










namespace Symfony\Component\HttpKernel\ControllerMetadata;






class ArgumentMetadata
{
public const IS_INSTANCEOF = 2;




public function __construct(
private string $name,
private ?string $type,
private bool $isVariadic,
private bool $hasDefaultValue,
private mixed $defaultValue,
private bool $isNullable = false,
private array $attributes = [],
private string $controllerName = 'n/a',
) {
$this->isNullable = $isNullable || null === $type || ($hasDefaultValue && null === $defaultValue);
}




public function getName(): string
{
return $this->name;
}






public function getType(): ?string
{
return $this->type;
}




public function isVariadic(): bool
{
return $this->isVariadic;
}






public function hasDefaultValue(): bool
{
return $this->hasDefaultValue;
}




public function isNullable(): bool
{
return $this->isNullable;
}






public function getDefaultValue(): mixed
{
if (!$this->hasDefaultValue) {
throw new \LogicException(sprintf('Argument $%s does not have a default value. Use "%s::hasDefaultValue()" to avoid this exception.', $this->name, __CLASS__));
}

return $this->defaultValue;
}







public function getAttributes(?string $name = null, int $flags = 0): array
{
if (!$name) {
return $this->attributes;
}

return $this->getAttributesOfType($name, $flags);
}

/**
@template





*/
public function getAttributesOfType(string $name, int $flags = 0): array
{
$attributes = [];
if ($flags & self::IS_INSTANCEOF) {
foreach ($this->attributes as $attribute) {
if ($attribute instanceof $name) {
$attributes[] = $attribute;
}
}
} else {
foreach ($this->attributes as $attribute) {
if ($attribute::class === $name) {
$attributes[] = $attribute;
}
}
}

return $attributes;
}

public function getControllerName(): string
{
return $this->controllerName;
}
}
