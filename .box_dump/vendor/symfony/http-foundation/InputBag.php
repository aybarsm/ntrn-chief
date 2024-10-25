<?php










namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\UnexpectedValueException;






final class InputBag extends ParameterBag
{





public function get(string $key, mixed $default = null): string|int|float|bool|null
{
if (null !== $default && !\is_scalar($default) && !$default instanceof \Stringable) {
throw new \InvalidArgumentException(sprintf('Expected a scalar value as a 2nd argument to "%s()", "%s" given.', __METHOD__, get_debug_type($default)));
}

$value = parent::get($key, $this);

if (null !== $value && $this !== $value && !\is_scalar($value) && !$value instanceof \Stringable) {
throw new BadRequestException(sprintf('Input value "%s" contains a non-scalar value.', $key));
}

return $this === $value ? $default : $value;
}




public function replace(array $inputs = []): void
{
$this->parameters = [];
$this->add($inputs);
}




public function add(array $inputs = []): void
{
foreach ($inputs as $input => $value) {
$this->set($input, $value);
}
}






public function set(string $key, mixed $value): void
{
if (null !== $value && !\is_scalar($value) && !\is_array($value) && !$value instanceof \Stringable) {
throw new \InvalidArgumentException(sprintf('Expected a scalar, or an array as a 2nd argument to "%s()", "%s" given.', __METHOD__, get_debug_type($value)));
}

$this->parameters[$key] = $value;
}

/**
@template
@psalm-return($default is null ? T|null : T)








*/
public function getEnum(string $key, string $class, ?\BackedEnum $default = null): ?\BackedEnum
{
try {
return parent::getEnum($key, $class, $default);
} catch (UnexpectedValueException $e) {
throw new BadRequestException($e->getMessage(), $e->getCode(), $e);
}
}




public function getString(string $key, string $default = ''): string
{

return (string) $this->get($key, $default);
}

public function filter(string $key, mixed $default = null, int $filter = \FILTER_DEFAULT, mixed $options = []): mixed
{
$value = $this->has($key) ? $this->all()[$key] : $default;


if (!\is_array($options) && $options) {
$options = ['flags' => $options];
}

if (\is_array($value) && !(($options['flags'] ?? 0) & (\FILTER_REQUIRE_ARRAY | \FILTER_FORCE_ARRAY))) {
throw new BadRequestException(sprintf('Input value "%s" contains an array, but "FILTER_REQUIRE_ARRAY" or "FILTER_FORCE_ARRAY" flags were not set.', $key));
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

throw new BadRequestException(sprintf('Input value "%s" is invalid and flag "FILTER_NULL_ON_FAILURE" was not set.', $key));
}
}
