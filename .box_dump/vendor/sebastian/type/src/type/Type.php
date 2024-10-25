<?php declare(strict_types=1);








namespace SebastianBergmann\Type;

use function gettype;
use function strtolower;

abstract class Type
{
public static function fromValue(mixed $value, bool $allowsNull): self
{
if ($allowsNull === false) {
if ($value === true) {
return new TrueType;
}

if ($value === false) {
return new FalseType;
}
}

$typeName = gettype($value);

if ($typeName === 'object') {
return new ObjectType(TypeName::fromQualifiedName($value::class), $allowsNull);
}

$type = self::fromName($typeName, $allowsNull);

if ($type instanceof SimpleType) {
$type = new SimpleType($typeName, $allowsNull, $value);
}

return $type;
}

public static function fromName(string $typeName, bool $allowsNull): self
{
return match (strtolower($typeName)) {
'callable' => new CallableType($allowsNull),
'true' => new TrueType,
'false' => new FalseType,
'iterable' => new IterableType($allowsNull),
'never' => new NeverType,
'null' => new NullType,
'object' => new GenericObjectType($allowsNull),
'unknown type' => new UnknownType,
'void' => new VoidType,
'array', 'bool', 'boolean', 'double', 'float', 'int', 'integer', 'real', 'resource', 'resource (closed)', 'string' => new SimpleType($typeName, $allowsNull),
'mixed' => new MixedType,
default => new ObjectType(TypeName::fromQualifiedName($typeName), $allowsNull),
};
}

public function asString(): string
{
return ($this->allowsNull() ? '?' : '') . $this->name();
}

/**
@psalm-assert-if-true
*/
public function isCallable(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isTrue(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isFalse(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isGenericObject(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isIntersection(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isIterable(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isMixed(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isNever(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isNull(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isObject(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isSimple(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isStatic(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isUnion(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isUnknown(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isVoid(): bool
{
return false;
}

abstract public function isAssignable(self $other): bool;

abstract public function name(): string;

abstract public function allowsNull(): bool;
}
