<?php

declare(strict_types=1);

namespace Pest;

use Attribute;
use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use OutOfRangeException;
use Pest\Arch\Contracts\ArchExpectation;
use Pest\Arch\Expectations\Targeted;
use Pest\Arch\Expectations\ToBeUsedIn;
use Pest\Arch\Expectations\ToBeUsedInNothing;
use Pest\Arch\Expectations\ToOnlyBeUsedIn;
use Pest\Arch\Expectations\ToOnlyUse;
use Pest\Arch\Expectations\ToUse;
use Pest\Arch\Expectations\ToUseNothing;
use Pest\Arch\PendingArchExpectation;
use Pest\Arch\Support\FileLineFinder;
use Pest\Concerns\Extendable;
use Pest\Concerns\Pipeable;
use Pest\Concerns\Retrievable;
use Pest\Exceptions\ExpectationNotFound;
use Pest\Exceptions\InvalidExpectation;
use Pest\Exceptions\InvalidExpectationValue;
use Pest\Expectations\EachExpectation;
use Pest\Expectations\HigherOrderExpectation;
use Pest\Expectations\OppositeExpectation;
use Pest\Matchers\Any;
use Pest\Support\ExpectationPipeline;
use PHPUnit\Architecture\Elements\ObjectDescription;
use PHPUnit\Framework\ExpectationFailedException;
use ReflectionEnum;

/**
@template
@mixin
@mixin








*/
final class Expectation
{
use Extendable;
use Pipeable;
use Retrievable;






public function __construct(
public mixed $value
) {

}

/**
@template





*/
public function and(mixed $value): Expectation
{
return $value instanceof self ? $value : new self($value);
}






public function json(): Expectation
{
if (! is_string($this->value)) {
InvalidExpectationValue::expected('string');
}

$this->toBeJson();


$value = json_decode($this->value, true, 512, JSON_THROW_ON_ERROR);

return $this->and($value);
}






public function dump(mixed ...$arguments): self
{
if (function_exists('dump')) {
dump($this->value, ...$arguments);
} else {
var_dump($this->value);
}

return $this;
}






public function dd(mixed ...$arguments): void
{
if (function_exists('dd')) {
dd($this->value, ...$arguments);
}

var_dump($this->value);

exit(1);
}







public function ddWhen(Closure|bool $condition, mixed ...$arguments): Expectation
{
$condition = $condition instanceof Closure ? $condition($this->value) : $condition;

if (! $condition) {
return $this;
}

$this->dd(...$arguments);
}







public function ddUnless(Closure|bool $condition, mixed ...$arguments): Expectation
{
$condition = $condition instanceof Closure ? $condition($this->value) : $condition;

if ($condition) {
return $this;
}

$this->dd(...$arguments);
}






public function ray(mixed ...$arguments): self
{
if (function_exists('ray')) {
ray($this->value, ...$arguments);
}

return $this;
}






public function not(): OppositeExpectation
{
return new OppositeExpectation($this);
}






public function each(?callable $callback = null): EachExpectation
{
if (! is_iterable($this->value)) {
throw new BadMethodCallException('Expectation value is not iterable.');
}

if (is_callable($callback)) {
foreach ($this->value as $key => $item) {
$callback(new self($item), $key);
}
}

return new EachExpectation($this);
}

/**
@template





*/
public function sequence(mixed ...$callbacks): self
{
if (! is_iterable($this->value)) {
throw new BadMethodCallException('Expectation value is not iterable.');
}

if (count($callbacks) == 0) {
throw new InvalidArgumentException('No sequence expectations defined.');
}

$index = $valuesCount = 0;

foreach ($this->value as $key => $value) {
$valuesCount++;

if ($callbacks[$index] instanceof Closure) {
$callbacks[$index](new self($value), new self($key));
} else {
(new self($value))->toEqual($callbacks[$index]);
}

$index = isset($callbacks[$index + 1]) ? $index + 1 : 0;
}

if ($valuesCount < count($callbacks)) {
throw new OutOfRangeException('Sequence expectations are more than the iterable items.');
}

return $this;
}

/**
@template






*/
public function match(mixed $subject, array $expressions): self
{
$subject = $subject instanceof Closure ? $subject() : $subject;

$matched = false;

foreach ($expressions as $key => $callback) {
if ($subject != $key) {
continue;
}

$matched = true;

if (is_callable($callback)) {
$callback(new self($this->value));

continue;
}

$this->and($this->value)->toEqual($callback);

break;
}

if ($matched === false) {
throw new ExpectationFailedException('Unhandled match value.');
}

return $this;
}








public function unless(callable|bool $condition, callable $callback): Expectation
{
$condition = is_callable($condition)
? $condition
: static fn (): bool => $condition;

return $this->when(! $condition(), $callback);
}








public function when(callable|bool $condition, callable $callback): self
{
$condition = is_callable($condition)
? $condition
: static fn (): bool => $condition;

if ($condition()) {
$callback($this->and($this->value));
}

return $this;
}







public function __call(string $method, array $parameters): Expectation|HigherOrderExpectation|PendingArchExpectation
{
if (! self::hasMethod($method)) {
if (! is_object($this->value) && method_exists(PendingArchExpectation::class, $method)) {
$pendingArchExpectation = new PendingArchExpectation($this, []);

return $pendingArchExpectation->$method(...$parameters); 
}

if (! is_object($this->value)) {
throw new BadMethodCallException(sprintf(
'Method "%s" does not exist in %s.',
$method,
gettype($this->value)
));
}


return new HigherOrderExpectation($this, call_user_func_array($this->value->$method(...), $parameters));
}

$closure = $this->getExpectationClosure($method);
$reflectionClosure = new \ReflectionFunction($closure);
$expectation = $reflectionClosure->getClosureThis();

assert(is_object($expectation));

ExpectationPipeline::for($closure)
->send(...$parameters)
->through($this->pipes($method, $expectation, Expectation::class))
->run();

return $this;
}






private function getExpectationClosure(string $name): Closure
{
if (method_exists(Mixins\Expectation::class, $name)) {

return Closure::fromCallable([new Mixins\Expectation($this->value), $name]);
}

if (self::hasExtend($name)) {
$extend = self::$extends[$name]->bindTo($this, Expectation::class);

if ($extend != false) {
return $extend;
}
}

throw ExpectationNotFound::fromName($name);
}






public function __get(string $name)
{
if (! self::hasMethod($name)) {
if (! is_object($this->value) && method_exists(PendingArchExpectation::class, $name)) {

return $this->{$name}();
}


return new HigherOrderExpectation($this, $this->retrieve($name, $this->value));
}


return $this->{$name}();
}




public static function hasMethod(string $name): bool
{
return method_exists(self::class, $name)
|| method_exists(Mixins\Expectation::class, $name)
|| self::hasExtend($name);
}




public function any(): Any
{
return new Any;
}






public function toUse(array|string $targets): ArchExpectation
{
return ToUse::make($this, $targets);
}




public function toUseStrictTypes(): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => (bool) preg_match('/^<\?php\s+declare\(.*?strict_types\s?=\s?1.*?\);/', (string) file_get_contents($object->path)),
'to use strict types',
FileLineFinder::where(fn (string $line): bool => str_contains($line, '<?php')),
);
}




public function toBeFinal(): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => ! enum_exists($object->name) && $object->reflectionClass->isFinal(),
'to be final',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toBeReadonly(): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => ! enum_exists($object->name) && $object->reflectionClass->isReadOnly() && assert(true), 
'to be readonly',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toBeTrait(): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => $object->reflectionClass->isTrait(),
'to be trait',
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
$this,
fn (ObjectDescription $object): bool => $object->reflectionClass->isAbstract(),
'to be abstract',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toHaveMethod(string $method): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => $object->reflectionClass->hasMethod($method),
sprintf("to have method '%s'", $method),
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toBeEnum(): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => $object->reflectionClass->isEnum(),
'to be enum',
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
$this,
fn (ObjectDescription $object): bool => class_exists($object->name) && ! enum_exists($object->name),
'to be class',
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
$this,
fn (ObjectDescription $object): bool => $object->reflectionClass->isInterface(),
'to be interface',
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
$this,
fn (ObjectDescription $object): bool => $class === $object->reflectionClass->getName() || $object->reflectionClass->isSubclassOf($class),
sprintf("to extend '%s'", $class),
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toExtendNothing(): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => $object->reflectionClass->getParentClass() === false,
'to extend nothing',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toImplementNothing(): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => $object->reflectionClass->getInterfaceNames() === [],
'to implement nothing',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}






public function toOnlyImplement(array|string $interfaces): ArchExpectation
{
$interfaces = is_array($interfaces) ? $interfaces : [$interfaces];

return Targeted::make(
$this,
fn (ObjectDescription $object): bool => count($interfaces) === count($object->reflectionClass->getInterfaceNames())
&& array_diff($interfaces, $object->reflectionClass->getInterfaceNames()) === [],
"to only implement '".implode("', '", $interfaces)."'",
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toHavePrefix(string $prefix): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => str_starts_with($object->reflectionClass->getShortName(), $prefix),
"to have prefix '{$prefix}'",
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}




public function toHaveSuffix(string $suffix): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => str_ends_with($object->reflectionClass->getName(), $suffix),
"to have suffix '{$suffix}'",
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}






public function toImplement(array|string $interfaces): ArchExpectation
{
$interfaces = is_array($interfaces) ? $interfaces : [$interfaces];

return Targeted::make(
$this,
function (ObjectDescription $object) use ($interfaces): bool {
foreach ($interfaces as $interface) {
if (! $object->reflectionClass->implementsInterface($interface)) {
return false;
}
}

return true;
},
"to implement '".implode("', '", $interfaces)."'",
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
}






public function toOnlyUse(array|string $targets): ArchExpectation
{
return ToOnlyUse::make($this, $targets);
}




public function toUseNothing(): ArchExpectation
{
return ToUseNothing::make($this);
}

public function toBeUsed(): never
{
throw InvalidExpectation::fromMethods(['toBeUsed']);
}






public function toBeUsedIn(array|string $targets): ArchExpectation
{
return ToBeUsedIn::make($this, $targets);
}






public function toOnlyBeUsedIn(array|string $targets): ArchExpectation
{
return ToOnlyBeUsedIn::make($this, $targets);
}




public function toBeUsedInNothing(): ArchExpectation
{
return ToBeUsedInNothing::make($this);
}




public function toBeInvokable(): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => $object->reflectionClass->hasMethod('__invoke'),
'to be invokable',
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class'))
);
}






public function toHaveSnakeCaseKeys(string $message = ''): self
{
if (! is_iterable($this->value)) {
InvalidExpectationValue::expected('iterable');
}

foreach ($this->value as $k => $item) {
if (is_string($k)) {
$this->and($k)->toBeSnakeCase($message);
}

if (is_array($item)) {
$this->and($item)->toHaveSnakeCaseKeys($message);
}
}

return $this;
}






public function toHaveKebabCaseKeys(string $message = ''): self
{
if (! is_iterable($this->value)) {
InvalidExpectationValue::expected('iterable');
}

foreach ($this->value as $k => $item) {
if (is_string($k)) {
$this->and($k)->toBeKebabCase($message);
}

if (is_array($item)) {
$this->and($item)->toHaveKebabCaseKeys($message);
}
}

return $this;
}






public function toHaveCamelCaseKeys(string $message = ''): self
{
if (! is_iterable($this->value)) {
InvalidExpectationValue::expected('iterable');
}

foreach ($this->value as $k => $item) {
if (is_string($k)) {
$this->and($k)->toBeCamelCase($message);
}

if (is_array($item)) {
$this->and($item)->toHaveCamelCaseKeys($message);
}
}

return $this;
}






public function toHaveStudlyCaseKeys(string $message = ''): self
{
if (! is_iterable($this->value)) {
InvalidExpectationValue::expected('iterable');
}

foreach ($this->value as $k => $item) {
if (is_string($k)) {
$this->and($k)->toBeStudlyCase($message);
}

if (is_array($item)) {
$this->and($item)->toHaveStudlyCaseKeys($message);
}
}

return $this;
}






public function toHaveAttribute(string $attribute): ArchExpectation
{
return Targeted::make(
$this,
fn (ObjectDescription $object): bool => $object->reflectionClass->getAttributes($attribute) !== [],
"to have attribute '{$attribute}'",
FileLineFinder::where(fn (string $line): bool => str_contains($line, 'class')),
);
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
$this,
fn (ObjectDescription $object): bool => $object->reflectionClass->isEnum()
&& (new ReflectionEnum($object->name))->isBacked() 
&& (string) (new ReflectionEnum($object->name))->getBackingType() === $backingType, 
'to be '.$backingType.' backed enum',
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
