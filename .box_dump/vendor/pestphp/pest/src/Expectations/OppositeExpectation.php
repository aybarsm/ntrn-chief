<?php

declare(strict_types=1);

namespace Pest\Expectations;

use Attribute;
use Pest\Arch\Contracts\ArchExpectation;
use Pest\Arch\Expectations\Targeted;
use Pest\Arch\Expectations\ToBeUsedIn;
use Pest\Arch\Expectations\ToBeUsedInNothing;
use Pest\Arch\Expectations\ToUse;
use Pest\Arch\GroupArchExpectation;
use Pest\Arch\PendingArchExpectation;
use Pest\Arch\SingleArchExpectation;
use Pest\Arch\Support\FileLineFinder;
use Pest\Exceptions\InvalidExpectation;
use Pest\Expectation;
use Pest\Support\Arr;
use Pest\Support\Exporter;
use PHPUnit\Architecture\Elements\ObjectDescription;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;

/**
@template
@mixin



*/
final class OppositeExpectation
{





public function __construct(private readonly Expectation $original) {}







public function toHaveKeys(array $keys): Expectation
{
foreach ($keys as $k => $key) {
try {
if (is_array($key)) {
$this->toHaveKeys(array_keys(Arr::dot($key, $k.'.')));
} else {
$this->original->toHaveKey($key);
}
} catch (ExpectationFailedException) {
continue;
}

$this->throwExpectationFailedException('toHaveKey', [$key]);
}

return $this->original;
}






public function toUse(array|string $targets): ArchExpectation
{
return GroupArchExpectation::fromExpectations($this->original, array_map(fn (string $target): SingleArchExpectation => ToUse::make($this->original, $target)->opposite(
fn () => $this->throwExpectationFailedException('toUse', $target),
), is_string($targets) ? [$targets] : $targets));
}




public function toUseStrictTypes(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! (bool) preg_match('/^<\?php\s+declare\(.*?strict_types\s?=\s?1.*?\);/', (string) file_get_contents($object->path)),
'not to use strict types',
FileLineFinder::where(fn (string $line): bool => str_contains($line, '<?php')),
);
}




public function toBeFinal(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! enum_exists($object->name) && ! $object->reflectionClass->isFinal(),
'not to be final',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toBeReadonly(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! enum_exists($object->name) && ! $object->reflectionClass->isReadOnly() && assert(true), 
'not to be readonly',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toBeTrait(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! $object->reflectionClass->isTrait(),
'not to be trait',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toBeTraits(): ArchExpectation
{
return $this->toBeTrait();
}




public function toBeAbstract(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! $object->reflectionClass->isAbstract(),
'not to be abstract',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toHaveMethod(string $method): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! $object->reflectionClass->hasMethod($method),
'to not have method',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toBeEnum(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! $object->reflectionClass->isEnum(),
'not to be enum',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toBeEnums(): ArchExpectation
{
return $this->toBeEnum();
}




public function toBeClass(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! class_exists($object->name),
'not to be class',
FileLineFinder::where(fn (string $line): bool => true),
);
}




public function toBeClasses(): ArchExpectation
{
return $this->toBeClass();
}




public function toBeInterface(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! $object->reflectionClass->isInterface(),
'not to be interface',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toBeInterfaces(): ArchExpectation
{
return $this->toBeInterface();
}






public function toExtend(string $class): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! $object->reflectionClass->isSubclassOf($class),
sprintf("not to extend '%s'", $class),
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toExtendNothing(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => $object->reflectionClass->getParentClass() !== false,
'to extend a class',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}






public function toImplement(array|string $interfaces): ArchExpectation
{
$interfaces = is_array($interfaces) ? $interfaces : [$interfaces];

return Targeted::make(
$this->original,
function (ObjectDescription $object) use ($interfaces): bool {
foreach ($interfaces as $interface) {
if ($object->reflectionClass->implementsInterface($interface)) {
return false;
}
}

return true;
},
"not to implement '".implode("', '", $interfaces)."'",
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toImplementNothing(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => $object->reflectionClass->getInterfaceNames() !== [],
'to implement an interface',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}






public function toOnlyImplement(array|string $interfaces): never
{
throw InvalidExpectation::fromMethods(['not', 'toOnlyImplement']);
}




public function toHavePrefix(string $prefix): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! str_starts_with($object->reflectionClass->getShortName(), $prefix),
"not to have prefix '{$prefix}'",
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toHaveSuffix(string $suffix): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! str_ends_with($object->reflectionClass->getName(), $suffix),
"not to have suffix '{$suffix}'",
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}






public function toOnlyUse(array|string $targets): never
{
throw InvalidExpectation::fromMethods(['not', 'toOnlyUse']);
}




public function toUseNothing(): never
{
throw InvalidExpectation::fromMethods(['not', 'toUseNothing']);
}




public function toBeUsed(): ArchExpectation
{
return ToBeUsedInNothing::make($this->original);
}






public function toBeUsedIn(array|string $targets): ArchExpectation
{
return GroupArchExpectation::fromExpectations($this->original, array_map(fn (string $target): GroupArchExpectation => ToBeUsedIn::make($this->original, $target)->opposite(
fn () => $this->throwExpectationFailedException('toBeUsedIn', $target),
), is_string($targets) ? [$targets] : $targets));
}

public function toOnlyBeUsedIn(): never
{
throw InvalidExpectation::fromMethods(['not', 'toOnlyBeUsedIn']);
}




public function toBeUsedInNothing(): never
{
throw InvalidExpectation::fromMethods(['not', 'toBeUsedInNothing']);
}




public function toBeInvokable(): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! $object->reflectionClass->hasMethod('__invoke'),
'to not be invokable',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class'))
);
}






public function toHaveAttribute(string $attribute): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => $object->reflectionClass->getAttributes($attribute) === [],
"to not have attribute '{$attribute}'",
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class'))
);
}







public function __call(string $name, array $arguments): Expectation
{
try {
if (! is_object($this->original->value) && method_exists(PendingArchExpectation::class, $name)) {
throw InvalidExpectation::fromMethods(['not', $name]);
}


$this->original->{$name}(...$arguments);
} catch (ExpectationFailedException|AssertionFailedError) {
return $this->original;
}

$this->throwExpectationFailedException($name, $arguments);
}






public function __get(string $name): Expectation
{
try {
if (! is_object($this->original->value) && method_exists(PendingArchExpectation::class, $name)) {
throw InvalidExpectation::fromMethods(['not', $name]);
}

$this->original->{$name}; 
} catch (ExpectationFailedException) {
return $this->original;
}

$this->throwExpectationFailedException($name);
}






public function throwExpectationFailedException(string $name, array|string $arguments = []): never
{
$arguments = is_array($arguments) ? $arguments : [$arguments];

$exporter = Exporter::default();

$toString = fn (mixed $argument): string => $exporter->shortenedExport($argument);

throw new ExpectationFailedException(sprintf(
'Expecting %s not %s %s.',
$toString($this->original->value),
strtolower((string) preg_replace('/(?<!\ )[A-Z]/', ' $0', $name)),
implode(' ', array_map(fn (mixed $argument): string => $toString($argument), $arguments)),
));
}




public function toHaveConstructor(): ArchExpectation
{
return $this->toHaveMethod('__construct');
}




public function toHaveDestructor(): ArchExpectation
{
return $this->toHaveMethod('__destruct');
}




private function toBeBackedEnum(string $backingType): ArchExpectation
{
return Targeted::make(
$this->original,
fn (ObjectDescription $object): bool => ! $object->reflectionClass->isEnum()
|| ! (new \ReflectionEnum($object->name))->isBacked() 
|| (string) (new \ReflectionEnum($object->name))->getBackingType() !== $backingType, 
'not to be '.$backingType.' backed enum',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toBeStringBackedEnums(): ArchExpectation
{
return $this->toBeStringBackedEnum();
}




public function toBeIntBackedEnums(): ArchExpectation
{
return $this->toBeIntBackedEnum();
}




public function toBeStringBackedEnum(): ArchExpectation
{
return $this->toBeBackedEnum('string');
}




public function toBeIntBackedEnum(): ArchExpectation
{
return $this->toBeBackedEnum('int');
}
}
