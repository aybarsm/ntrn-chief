<?php










namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\UnexpectedValueException;

/**
@implements




*/
class ParameterBag implements \IteratorAggregate, \Countable
{
protected array $parameters;

public function __construct(array $parameters = [])
{
$this->parameters = $parameters;
}






public function all(?string $key = null): array
{
if (null === $key) {
return $this->parameters;
}

if (!\is_array($value = $this->parameters[$key] ?? [])) {
throw new BadRequestException(sprintf('Unexpected value for parameter "%s": expecting "array", got "%s".', $key, get_debug_type($value)));
}

return $value;
}




public function keys(): array
{
return array_keys($this->parameters);
}




public function replace(array $parameters = []): void
{
$this->parameters = $parameters;
}




public function add(array $parameters = []): void
{
$this->parameters = array_replace($this->parameters, $parameters);
}

public function get(string $key, mixed $default = null): mixed
{
return \array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
}

public function set(string $key, mixed $value): void
{
$this->parameters[$key] = $value;
}




public function has(string $key): bool
{
return \array_key_exists($key, $this->parameters);
}




public function remove(string $key): void
{
unset($this->parameters[$key]);
}




public function getAlpha(string $key, string $default = ''): string
{
return preg_replace('/[^[:alpha:]]/', '', $this->getString($key, $default));
}




public function getAlnum(string $key, string $default = ''): string
{
return preg_replace('/[^[:alnum:]]/', '', $this->getString($key, $default));
}




public function getDigits(string $key, string $default = ''): string
{
return preg_replace('/[^[:digit:]]/', '', $this->getString($key, $default));
}




public function getString(string $key, string $default = ''): string
{
$value = $this->get($key, $default);
if (!\is_scalar($value) && !$value instanceof \Stringable) {
throw new UnexpectedValueException(sprintf('Parameter value "%s" cannot be converted to "string".', $key));
}

return (string) $value;
}




public function getInt(string $key, int $default = 0): int
{
return $this->filter($key, $default, \FILTER_VALIDATE_INT, ['flags' => \FILTER_REQUIRE_SCALAR]);
}




public function getBoolean(string $key, bool $default = false): bool
{
return $this->filter($key, $default, \FILTER_VALIDATE_BOOL, ['flags' => \FILTER_REQUIRE_SCALAR]);
}

/**
@template
@psalm-return($default is null ? T|null : T)








*/
public function getEnum(string $key, string $class, ?\BackedEnum $default = null): ?\BackedEnum
{
$value = $this->get($key);

if (null === $value) {
return $default;
}

try {
return $class::from($value);
} catch (\ValueError|\TypeError $e) {
throw new UnexpectedValueException(sprintf('Parameter "%s" cannot be converted to enum: %s.', $key, $e->getMessage()), $e->getCode(), $e);
}
}









public function filter(string $key, mixed $default = null, int $filter = \FILTER_DEFAULT, mixed $options = []): mixed
{
$value = $this->get($key, $default);


if (!\is_array($options) && $options) {
$options = ['flags' => $options];
}


if (\is_array($value) && !isset($options['flags'])) {
$options['flags'] = \FILTER_REQUIRE_ARRAY;
}

if (\is_object($value) && !$value instanceof \Stringable) {
throw new UnexpectedValueException(sprintf('Parameter value "%s" cannot be filtered.', $key));
}

if ((\FILTER_CALLBACK & $filter) && !(($options['options'] ?? null) instanceof \Closure)) {
throw new \InvalidArgumentException(sprintf('A Closure must be passed to "%s()" when FILTER_CALLBACK is used, "%s" given.', __METHOD__, get_debug_type($options['options'] ?? null)));
}

$options['flags'] ??= 0;
$nullOnFailure = $options['flags'] & \FILTER_NULL_ON_FAILURE;
$options['flags'] |= \FILTER_NULL_ON_FAILURE;

$value = filter_var($value, $filter, $options);

if (null !== $value || $nullOnFailure) {
return $value;
}

throw new \UnexpectedValueException(sprintf('Parameter value "%s" is invalid and flag "FILTER_NULL_ON_FAILURE" was not set.', $key));
}






public function getIterator(): \ArrayIterator
{
return new \ArrayIterator($this->parameters);
}




public function count(): int
{
return \count($this->parameters);
}
}
