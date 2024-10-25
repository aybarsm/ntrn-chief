<?php













namespace Assert;

use ArrayAccess;
use BadMethodCallException;
use Countable;
use DateTime;
use ReflectionClass;
use ReflectionException;
use ResourceBundle;
use SimpleXMLElement;
use Throwable;
use Traversable;

























































































































































































class Assertion
{
const INVALID_FLOAT = 9;
const INVALID_INTEGER = 10;
const INVALID_DIGIT = 11;
const INVALID_INTEGERISH = 12;
const INVALID_BOOLEAN = 13;
const VALUE_EMPTY = 14;
const VALUE_NULL = 15;
const VALUE_NOT_NULL = 25;
const INVALID_STRING = 16;
const INVALID_REGEX = 17;
const INVALID_MIN_LENGTH = 18;
const INVALID_MAX_LENGTH = 19;
const INVALID_STRING_START = 20;
const INVALID_STRING_CONTAINS = 21;
const INVALID_CHOICE = 22;
const INVALID_NUMERIC = 23;
const INVALID_ARRAY = 24;
const INVALID_KEY_EXISTS = 26;
const INVALID_NOT_BLANK = 27;
const INVALID_INSTANCE_OF = 28;
const INVALID_SUBCLASS_OF = 29;
const INVALID_RANGE = 30;
const INVALID_ALNUM = 31;
const INVALID_TRUE = 32;
const INVALID_EQ = 33;
const INVALID_SAME = 34;
const INVALID_MIN = 35;
const INVALID_MAX = 36;
const INVALID_LENGTH = 37;
const INVALID_FALSE = 38;
const INVALID_STRING_END = 39;
const INVALID_UUID = 40;
const INVALID_COUNT = 41;
const INVALID_NOT_EQ = 42;
const INVALID_NOT_SAME = 43;
const INVALID_TRAVERSABLE = 44;
const INVALID_ARRAY_ACCESSIBLE = 45;
const INVALID_KEY_ISSET = 46;
const INVALID_VALUE_IN_ARRAY = 47;
const INVALID_E164 = 48;
const INVALID_BASE64 = 49;
const INVALID_NOT_REGEX = 50;
const INVALID_DIRECTORY = 101;
const INVALID_FILE = 102;
const INVALID_READABLE = 103;
const INVALID_WRITEABLE = 104;
const INVALID_CLASS = 105;
const INVALID_INTERFACE = 106;
const INVALID_FILE_NOT_EXISTS = 107;
const INVALID_EMAIL = 201;
const INTERFACE_NOT_IMPLEMENTED = 202;
const INVALID_URL = 203;
const INVALID_NOT_INSTANCE_OF = 204;
const VALUE_NOT_EMPTY = 205;
const INVALID_JSON_STRING = 206;
const INVALID_OBJECT = 207;
const INVALID_METHOD = 208;
const INVALID_SCALAR = 209;
const INVALID_LESS = 210;
const INVALID_LESS_OR_EQUAL = 211;
const INVALID_GREATER = 212;
const INVALID_GREATER_OR_EQUAL = 213;
const INVALID_DATE = 214;
const INVALID_CALLABLE = 215;
const INVALID_KEY_NOT_EXISTS = 216;
const INVALID_SATISFY = 217;
const INVALID_IP = 218;
const INVALID_BETWEEN = 219;
const INVALID_BETWEEN_EXCLUSIVE = 220;
const INVALID_EXTENSION = 222;
const INVALID_CONSTANT = 221;
const INVALID_VERSION = 223;
const INVALID_PROPERTY = 224;
const INVALID_RESOURCE = 225;
const INVALID_COUNTABLE = 226;
const INVALID_MIN_COUNT = 227;
const INVALID_MAX_COUNT = 228;
const INVALID_STRING_NOT_CONTAINS = 229;
const INVALID_UNIQUE_VALUES = 230;






protected static $exceptionClass = InvalidArgumentException::class;










public static function eq($value, $value2, $message = null, string $propertyPath = null): bool
{
if ($value != $value2) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" does not equal expected value "%s".'),
static::stringify($value),
static::stringify($value2)
);

throw static::createException($value, $message, static::INVALID_EQ, $propertyPath, ['expected' => $value2]);
}

return true;
}










public static function eqArraySubset($value, $value2, $message = null, string $propertyPath = null): bool
{
static::isArray($value, $message, $propertyPath);
static::isArray($value2, $message, $propertyPath);

$patched = \array_replace_recursive($value, $value2);
static::eq($patched, $value, $message, $propertyPath);

return true;
}

/**
@psalm-template
@psalm-param
@psalm-assert











*/
public static function same($value, $value2, $message = null, string $propertyPath = null): bool
{
if ($value !== $value2) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not the same as expected value "%s".'),
static::stringify($value),
static::stringify($value2)
);

throw static::createException($value, $message, static::INVALID_SAME, $propertyPath, ['expected' => $value2]);
}

return true;
}










public static function notEq($value1, $value2, $message = null, string $propertyPath = null): bool
{
if ($value1 == $value2) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" was not expected to be equal to value "%s".'),
static::stringify($value1),
static::stringify($value2)
);
throw static::createException($value1, $message, static::INVALID_NOT_EQ, $propertyPath, ['expected' => $value2]);
}

return true;
}

/**
@psalm-template
@psalm-param
@psalm-assert











*/
public static function notSame($value1, $value2, $message = null, string $propertyPath = null): bool
{
if ($value1 === $value2) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" was not expected to be the same as value "%s".'),
static::stringify($value1),
static::stringify($value2)
);
throw static::createException($value1, $message, static::INVALID_NOT_SAME, $propertyPath, ['expected' => $value2]);
}

return true;
}









public static function notInArray($value, array $choices, $message = null, string $propertyPath = null): bool
{
if (true === \in_array($value, $choices)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" was not expected to be an element of the values: %s'),
static::stringify($value),
static::stringify($choices)
);
throw static::createException($value, $message, static::INVALID_VALUE_IN_ARRAY, $propertyPath, ['choices' => $choices]);
}

return true;
}

/**
@psalm-assert










*/
public static function integer($value, $message = null, string $propertyPath = null): bool
{
if (!\is_int($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not an integer.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_INTEGER, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function float($value, $message = null, string $propertyPath = null): bool
{
if (!\is_float($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not a float.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_FLOAT, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function digit($value, $message = null, string $propertyPath = null): bool
{
if (!\ctype_digit((string)$value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not a digit.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_DIGIT, $propertyPath);
}

return true;
}









public static function integerish($value, $message = null, string $propertyPath = null): bool
{
if (
\is_resource($value) ||
\is_object($value) ||
\is_bool($value) ||
\is_null($value) ||
\is_array($value) ||
(\is_string($value) && '' == $value) ||
(
\strval(\intval($value)) !== \strval($value) &&
\strval(\intval($value)) !== \strval(\ltrim($value, '0')) &&
'' !== \strval(\intval($value)) &&
'' !== \strval(\ltrim($value, '0'))
)
) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not an integer or a number castable to integer.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_INTEGERISH, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function boolean($value, $message = null, string $propertyPath = null): bool
{
if (!\is_bool($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not a boolean.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_BOOLEAN, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function scalar($value, $message = null, string $propertyPath = null): bool
{
if (!\is_scalar($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not a scalar.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_SCALAR, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function notEmpty($value, $message = null, string $propertyPath = null): bool
{
if (empty($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is empty, but non empty value was expected.'),
static::stringify($value)
);

throw static::createException($value, $message, static::VALUE_EMPTY, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function noContent($value, $message = null, string $propertyPath = null): bool
{
if (!empty($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not empty, but empty value was expected.'),
static::stringify($value)
);

throw static::createException($value, $message, static::VALUE_NOT_EMPTY, $propertyPath);
}

return true;
}

/**
@psalm-assert








*/
public static function null($value, $message = null, string $propertyPath = null): bool
{
if (null !== $value) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not null, but null value was expected.'),
static::stringify($value)
);

throw static::createException($value, $message, static::VALUE_NOT_NULL, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function notNull($value, $message = null, string $propertyPath = null): bool
{
if (null === $value) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is null, but non null value was expected.'),
static::stringify($value)
);

throw static::createException($value, $message, static::VALUE_NULL, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function string($value, $message = null, string $propertyPath = null)
{
if (!\is_string($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" expected to be string, type %s given.'),
static::stringify($value),
\gettype($value)
);

throw static::createException($value, $message, static::INVALID_STRING, $propertyPath);
}

return true;
}

/**
@psalm-assert











*/
public static function regex($value, $pattern, $message = null, string $propertyPath = null): bool
{
static::string($value, $message, $propertyPath);

if (!\preg_match($pattern, $value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" does not match expression.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_REGEX, $propertyPath, ['pattern' => $pattern]);
}

return true;
}

/**
@psalm-assert









*/
public static function notRegex($value, $pattern, $message = null, string $propertyPath = null): bool
{
static::string($value, $message, $propertyPath);

if (\preg_match($pattern, $value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" matches expression.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_NOT_REGEX, $propertyPath, ['pattern' => $pattern]);
}

return true;
}

/**
@psalm-assert












*/
public static function length($value, $length, $message = null, string $propertyPath = null, $encoding = 'utf8'): bool
{
static::string($value, $message, $propertyPath);

if (\mb_strlen($value, $encoding) !== $length) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" has to be %d exactly characters long, but length is %d.'),
static::stringify($value),
$length,
\mb_strlen($value, $encoding)
);

throw static::createException($value, $message, static::INVALID_LENGTH, $propertyPath, ['length' => $length, 'encoding' => $encoding]);
}

return true;
}

/**
@psalm-assert












*/
public static function minLength($value, $minLength, $message = null, string $propertyPath = null, $encoding = 'utf8'): bool
{
static::string($value, $message, $propertyPath);

if (\mb_strlen($value, $encoding) < $minLength) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is too short, it should have at least %d characters, but only has %d characters.'),
static::stringify($value),
$minLength,
\mb_strlen($value, $encoding)
);

throw static::createException($value, $message, static::INVALID_MIN_LENGTH, $propertyPath, ['min_length' => $minLength, 'encoding' => $encoding]);
}

return true;
}

/**
@psalm-assert












*/
public static function maxLength($value, $maxLength, $message = null, string $propertyPath = null, $encoding = 'utf8'): bool
{
static::string($value, $message, $propertyPath);

if (\mb_strlen($value, $encoding) > $maxLength) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is too long, it should have no more than %d characters, but has %d characters.'),
static::stringify($value),
$maxLength,
\mb_strlen($value, $encoding)
);

throw static::createException($value, $message, static::INVALID_MAX_LENGTH, $propertyPath, ['max_length' => $maxLength, 'encoding' => $encoding]);
}

return true;
}

/**
@psalm-assert













*/
public static function betweenLength($value, $minLength, $maxLength, $message = null, string $propertyPath = null, $encoding = 'utf8'): bool
{
static::string($value, $message, $propertyPath);
static::minLength($value, $minLength, $message, $propertyPath, $encoding);
static::maxLength($value, $maxLength, $message, $propertyPath, $encoding);

return true;
}

/**
@psalm-assert












*/
public static function startsWith($string, $needle, $message = null, string $propertyPath = null, $encoding = 'utf8'): bool
{
static::string($string, $message, $propertyPath);

if (0 !== \mb_strpos($string, $needle, 0, $encoding)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" does not start with "%s".'),
static::stringify($string),
static::stringify($needle)
);

throw static::createException($string, $message, static::INVALID_STRING_START, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
}

return true;
}

/**
@psalm-assert












*/
public static function endsWith($string, $needle, $message = null, string $propertyPath = null, $encoding = 'utf8'): bool
{
static::string($string, $message, $propertyPath);

$stringPosition = \mb_strlen($string, $encoding) - \mb_strlen($needle, $encoding);

if (\mb_strripos($string, $needle, 0, $encoding) !== $stringPosition) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" does not end with "%s".'),
static::stringify($string),
static::stringify($needle)
);

throw static::createException($string, $message, static::INVALID_STRING_END, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
}

return true;
}

/**
@psalm-assert












*/
public static function contains($string, $needle, $message = null, string $propertyPath = null, $encoding = 'utf8'): bool
{
static::string($string, $message, $propertyPath);

if (false === \mb_strpos($string, $needle, 0, $encoding)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" does not contain "%s".'),
static::stringify($string),
static::stringify($needle)
);

throw static::createException($string, $message, static::INVALID_STRING_CONTAINS, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
}

return true;
}

/**
@psalm-assert












*/
public static function notContains($string, $needle, $message = null, string $propertyPath = null, $encoding = 'utf8'): bool
{
static::string($string, $message, $propertyPath);

if (false !== \mb_strpos($string, $needle, 0, $encoding)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" contains "%s".'),
static::stringify($string),
static::stringify($needle)
);

throw static::createException($string, $message, static::INVALID_STRING_NOT_CONTAINS, $propertyPath, ['needle' => $needle, 'encoding' => $encoding]);
}

return true;
}









public static function choice($value, array $choices, $message = null, string $propertyPath = null): bool
{
if (!\in_array($value, $choices, true)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not an element of the valid values: %s'),
static::stringify($value),
\implode(', ', \array_map([\get_called_class(), 'stringify'], $choices))
);

throw static::createException($value, $message, static::INVALID_CHOICE, $propertyPath, ['choices' => $choices]);
}

return true;
}











public static function inArray($value, array $choices, $message = null, string $propertyPath = null): bool
{
return static::choice($value, $choices, $message, $propertyPath);
}

/**
@psalm-assert










*/
public static function numeric($value, $message = null, string $propertyPath = null): bool
{
if (!\is_numeric($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not numeric.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_NUMERIC, $propertyPath);
}

return true;
}

/**
@psalm-assert








*/
public static function isResource($value, $message = null, string $propertyPath = null): bool
{
if (!\is_resource($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not a resource.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_RESOURCE, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function isArray($value, $message = null, string $propertyPath = null): bool
{
if (!\is_array($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not an array.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_ARRAY, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function isTraversable($value, $message = null, string $propertyPath = null): bool
{
if (!\is_array($value) && !$value instanceof Traversable) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not an array and does not implement Traversable.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_TRAVERSABLE, $propertyPath);
}

return true;
}









public static function isArrayAccessible($value, $message = null, string $propertyPath = null): bool
{
if (!\is_array($value) && !$value instanceof ArrayAccess) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not an array and does not implement ArrayAccess.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_ARRAY_ACCESSIBLE, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function isCountable($value, $message = null, string $propertyPath = null): bool
{
if (\function_exists('is_countable')) {
$assert = \is_countable($value);
} else {
$assert = \is_array($value) || $value instanceof Countable || $value instanceof ResourceBundle || $value instanceof SimpleXMLElement;
}

if (!$assert) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not an array and does not implement Countable.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_COUNTABLE, $propertyPath);
}

return true;
}










public static function keyExists($value, $key, $message = null, string $propertyPath = null): bool
{
static::isArray($value, $message, $propertyPath);

if (!\array_key_exists($key, $value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Array does not contain an element with key "%s"'),
static::stringify($key)
);

throw static::createException($value, $message, static::INVALID_KEY_EXISTS, $propertyPath, ['key' => $key]);
}

return true;
}










public static function keyNotExists($value, $key, $message = null, string $propertyPath = null): bool
{
static::isArray($value, $message, $propertyPath);

if (\array_key_exists($key, $value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Array contains an element with key "%s"'),
static::stringify($key)
);

throw static::createException($value, $message, static::INVALID_KEY_NOT_EXISTS, $propertyPath, ['key' => $key]);
}

return true;
}









public static function uniqueValues(array $values, $message = null, string $propertyPath = null): bool
{
foreach ($values as $key => $value) {
if (\array_search($value, $values, true) !== $key) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" occurs more than once in array'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_UNIQUE_VALUES, $propertyPath, ['value' => $value]);
}
}

return true;
}










public static function keyIsset($value, $key, $message = null, string $propertyPath = null): bool
{
static::isArrayAccessible($value, $message, $propertyPath);

if (!isset($value[$key])) {
$message = \sprintf(
static::generateMessage($message ?: 'The element with key "%s" was not found'),
static::stringify($key)
);

throw static::createException($value, $message, static::INVALID_KEY_ISSET, $propertyPath, ['key' => $key]);
}

return true;
}










public static function notEmptyKey($value, $key, $message = null, string $propertyPath = null): bool
{
static::keyIsset($value, $key, $message, $propertyPath);
static::notEmpty($value[$key], $message, $propertyPath);

return true;
}









public static function notBlank($value, $message = null, string $propertyPath = null): bool
{
if (false === $value || (empty($value) && '0' != $value) || (\is_string($value) && '' === \trim($value))) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is blank, but was expected to contain a value.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_NOT_BLANK, $propertyPath);
}

return true;
}

/**
@psalm-template
@psalm-param
@psalm-assert











*/
public static function isInstanceOf($value, $className, $message = null, string $propertyPath = null): bool
{
if (!($value instanceof $className)) {
$message = \sprintf(
static::generateMessage($message ?: 'Class "%s" was expected to be instanceof of "%s" but is not.'),
static::stringify($value),
$className
);

throw static::createException($value, $message, static::INVALID_INSTANCE_OF, $propertyPath, ['class' => $className]);
}

return true;
}

/**
@psalm-template
@psalm-param
@psalm-assert











*/
public static function notIsInstanceOf($value, $className, $message = null, string $propertyPath = null): bool
{
if ($value instanceof $className) {
$message = \sprintf(
static::generateMessage($message ?: 'Class "%s" was not expected to be instanceof of "%s".'),
static::stringify($value),
$className
);

throw static::createException($value, $message, static::INVALID_NOT_INSTANCE_OF, $propertyPath, ['class' => $className]);
}

return true;
}










public static function subclassOf($value, $className, $message = null, string $propertyPath = null): bool
{
if (!\is_subclass_of($value, $className)) {
$message = \sprintf(
static::generateMessage($message ?: 'Class "%s" was expected to be subclass of "%s".'),
static::stringify($value),
$className
);

throw static::createException($value, $message, static::INVALID_SUBCLASS_OF, $propertyPath, ['class' => $className]);
}

return true;
}

/**
@psalm-assert












*/
public static function range($value, $minValue, $maxValue, $message = null, string $propertyPath = null): bool
{
static::numeric($value, $message, $propertyPath);

if ($value < $minValue || $value > $maxValue) {
$message = \sprintf(
static::generateMessage($message ?: 'Number "%s" was expected to be at least "%d" and at most "%d".'),
static::stringify($value),
static::stringify($minValue),
static::stringify($maxValue)
);

throw static::createException($value, $message, static::INVALID_RANGE, $propertyPath, ['min' => $minValue, 'max' => $maxValue]);
}

return true;
}

/**
@psalm-assert











*/
public static function min($value, $minValue, $message = null, string $propertyPath = null): bool
{
static::numeric($value, $message, $propertyPath);

if ($value < $minValue) {
$message = \sprintf(
static::generateMessage($message ?: 'Number "%s" was expected to be at least "%s".'),
static::stringify($value),
static::stringify($minValue)
);

throw static::createException($value, $message, static::INVALID_MIN, $propertyPath, ['min' => $minValue]);
}

return true;
}

/**
@psalm-assert











*/
public static function max($value, $maxValue, $message = null, string $propertyPath = null): bool
{
static::numeric($value, $message, $propertyPath);

if ($value > $maxValue) {
$message = \sprintf(
static::generateMessage($message ?: 'Number "%s" was expected to be at most "%s".'),
static::stringify($value),
static::stringify($maxValue)
);

throw static::createException($value, $message, static::INVALID_MAX, $propertyPath, ['max' => $maxValue]);
}

return true;
}









public static function file($value, $message = null, string $propertyPath = null): bool
{
static::string($value, $message, $propertyPath);
static::notEmpty($value, $message, $propertyPath);

if (!\is_file($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'File "%s" was expected to exist.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_FILE, $propertyPath);
}

return true;
}









public static function directory($value, $message = null, string $propertyPath = null): bool
{
static::string($value, $message, $propertyPath);

if (!\is_dir($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Path "%s" was expected to be a directory.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_DIRECTORY, $propertyPath);
}

return true;
}









public static function readable($value, $message = null, string $propertyPath = null): bool
{
static::string($value, $message, $propertyPath);

if (!\is_readable($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Path "%s" was expected to be readable.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_READABLE, $propertyPath);
}

return true;
}









public static function writeable($value, $message = null, string $propertyPath = null): bool
{
static::string($value, $message, $propertyPath);

if (!\is_writable($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Path "%s" was expected to be writeable.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_WRITEABLE, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function email($value, $message = null, string $propertyPath = null): bool
{
static::string($value, $message, $propertyPath);

if (!\filter_var($value, FILTER_VALIDATE_EMAIL)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" was expected to be a valid e-mail address.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_EMAIL, $propertyPath);
}

return true;
}

/**
@psalm-assert















*/
public static function url($value, $message = null, string $propertyPath = null): bool
{
static::string($value, $message, $propertyPath);

$protocols = ['http', 'https'];

$pattern = '~^
            (%s)://                                                             # protocol
            (([\.\pL\pN-]+:)?([\.\pL\pN-]+)@)?                                  # basic auth
            (
                ([\pL\pN\pS\-\.])+(\.?([\pL\pN]|xn\-\-[\pL\pN-]+)+\.?)          # a domain name
                |                                                               # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                              # an IP address
                |                                                               # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]                                                              # an IPv6 address
            )
            (:[0-9]+)?                                                          # a port (optional)
            (?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%%[0-9A-Fa-f]{2})* )*          # a path
            (?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?   # a query (optional)
            (?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?       # a fragment (optional)
        $~ixu';

$pattern = \sprintf($pattern, \implode('|', $protocols));

if (!\preg_match($pattern, $value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" was expected to be a valid URL starting with http or https'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_URL, $propertyPath);
}

return true;
}









public static function alnum($value, $message = null, string $propertyPath = null): bool
{
try {
static::regex($value, '(^([a-zA-Z]{1}[a-zA-Z0-9]*)$)', $message, $propertyPath);
} catch (Throwable $e) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not alphanumeric, starting with letters and containing only letters and numbers.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_ALNUM, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function true($value, $message = null, string $propertyPath = null): bool
{
if (true !== $value) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not TRUE.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_TRUE, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function false($value, $message = null, string $propertyPath = null): bool
{
if (false !== $value) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not FALSE.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_FALSE, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function classExists($value, $message = null, string $propertyPath = null): bool
{
if (!\class_exists($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Class "%s" does not exist.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_CLASS, $propertyPath);
}

return true;
}

/**
@psalm-assert










*/
public static function interfaceExists($value, $message = null, string $propertyPath = null): bool
{
if (!\interface_exists($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Interface "%s" does not exist.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_INTERFACE, $propertyPath);
}

return true;
}










public static function implementsInterface($class, $interfaceName, $message = null, string $propertyPath = null): bool
{
try {
$reflection = new ReflectionClass($class);
if (!$reflection->implementsInterface($interfaceName)) {
$message = \sprintf(
static::generateMessage($message ?: 'Class "%s" does not implement interface "%s".'),
static::stringify($class),
static::stringify($interfaceName)
);

throw static::createException($class, $message, static::INTERFACE_NOT_IMPLEMENTED, $propertyPath, ['interface' => $interfaceName]);
}
} catch (ReflectionException $e) {
$message = \sprintf(
static::generateMessage($message ?: 'Class "%s" failed reflection.'),
static::stringify($class)
);
throw static::createException($class, $message, static::INTERFACE_NOT_IMPLEMENTED, $propertyPath, ['interface' => $interfaceName]);
}

return true;
}

/**
@psalm-assert
















*/
public static function isJsonString($value, $message = null, string $propertyPath = null): bool
{
if (null === \json_decode($value) && JSON_ERROR_NONE !== \json_last_error()) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not a valid JSON string.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_JSON_STRING, $propertyPath);
}

return true;
}











public static function uuid($value, $message = null, string $propertyPath = null): bool
{
$value = \str_replace(['urn:', 'uuid:', '{', '}'], '', $value);

if ('00000000-0000-0000-0000-000000000000' === $value) {
return true;
}

if (!\preg_match('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/', $value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not a valid UUID.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_UUID, $propertyPath);
}

return true;
}











public static function e164($value, $message = null, string $propertyPath = null): bool
{
if (!\preg_match('/^\+?[1-9]\d{1,14}$/', $value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" is not a valid E164.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_E164, $propertyPath);
}

return true;
}













public static function count($countable, $count, $message = null, string $propertyPath = null): bool
{
if ($count !== \count($countable)) {
$message = \sprintf(
static::generateMessage($message ?: 'List does not contain exactly %d elements (%d given).'),
static::stringify($count),
static::stringify(\count($countable))
);

throw static::createException($countable, $message, static::INVALID_COUNT, $propertyPath, ['count' => $count]);
}

return true;
}










public static function minCount($countable, $count, $message = null, string $propertyPath = null): bool
{
if ($count > \count($countable)) {
$message = \sprintf(
static::generateMessage($message ?: 'List should have at least %d elements, but has %d elements.'),
static::stringify($count),
static::stringify(\count($countable))
);

throw static::createException($countable, $message, static::INVALID_MIN_COUNT, $propertyPath, ['count' => $count]);
}

return true;
}










public static function maxCount($countable, $count, $message = null, string $propertyPath = null): bool
{
if ($count < \count($countable)) {
$message = \sprintf(
static::generateMessage($message ?: 'List should have at most %d elements, but has %d elements.'),
static::stringify($count),
static::stringify(\count($countable))
);

throw static::createException($countable, $message, static::INVALID_MAX_COUNT, $propertyPath, ['count' => $count]);
}

return true;
}













public static function __callStatic($method, $args)
{
if (0 === \strpos($method, 'nullOr')) {
if (!\array_key_exists(0, $args)) {
throw new BadMethodCallException('Missing the first argument.');
}

if (null === $args[0]) {
return true;
}

$method = \substr($method, 6);

return \call_user_func_array([\get_called_class(), $method], $args);
}

if (0 === \strpos($method, 'all')) {
if (!\array_key_exists(0, $args)) {
throw new BadMethodCallException('Missing the first argument.');
}

static::isTraversable($args[0]);

$method = \substr($method, 3);
$values = \array_shift($args);
$calledClass = \get_called_class();

foreach ($values as $value) {
\call_user_func_array([$calledClass, $method], \array_merge([$value], $args));
}

return true;
}

throw new BadMethodCallException('No assertion Assertion#'.$method.' exists.');
}








public static function choicesNotEmpty(array $values, array $choices, $message = null, string $propertyPath = null): bool
{
static::notEmpty($values, $message, $propertyPath);

foreach ($choices as $choice) {
static::notEmptyKey($values, $choice, $message, $propertyPath);
}

return true;
}










public static function methodExists($value, $object, $message = null, string $propertyPath = null): bool
{
static::isObject($object, $message, $propertyPath);

if (!\method_exists($object, $value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Expected "%s" does not exist in provided object.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_METHOD, $propertyPath, ['object' => \get_class($object)]);
}

return true;
}

/**
@psalm-assert










*/
public static function isObject($value, $message = null, string $propertyPath = null): bool
{
if (!\is_object($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Provided "%s" is not a valid object.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_OBJECT, $propertyPath);
}

return true;
}










public static function lessThan($value, $limit, $message = null, string $propertyPath = null): bool
{
if ($value >= $limit) {
$message = \sprintf(
static::generateMessage($message ?: 'Provided "%s" is not less than "%s".'),
static::stringify($value),
static::stringify($limit)
);

throw static::createException($value, $message, static::INVALID_LESS, $propertyPath, ['limit' => $limit]);
}

return true;
}










public static function lessOrEqualThan($value, $limit, $message = null, string $propertyPath = null): bool
{
if ($value > $limit) {
$message = \sprintf(
static::generateMessage($message ?: 'Provided "%s" is not less or equal than "%s".'),
static::stringify($value),
static::stringify($limit)
);

throw static::createException($value, $message, static::INVALID_LESS_OR_EQUAL, $propertyPath, ['limit' => $limit]);
}

return true;
}










public static function greaterThan($value, $limit, $message = null, string $propertyPath = null): bool
{
if ($value <= $limit) {
$message = \sprintf(
static::generateMessage($message ?: 'Provided "%s" is not greater than "%s".'),
static::stringify($value),
static::stringify($limit)
);

throw static::createException($value, $message, static::INVALID_GREATER, $propertyPath, ['limit' => $limit]);
}

return true;
}










public static function greaterOrEqualThan($value, $limit, $message = null, string $propertyPath = null): bool
{
if ($value < $limit) {
$message = \sprintf(
static::generateMessage($message ?: 'Provided "%s" is not greater or equal than "%s".'),
static::stringify($value),
static::stringify($limit)
);

throw static::createException($value, $message, static::INVALID_GREATER_OR_EQUAL, $propertyPath, ['limit' => $limit]);
}

return true;
}












public static function between($value, $lowerLimit, $upperLimit, $message = null, string $propertyPath = null): bool
{
if ($lowerLimit > $value || $value > $upperLimit) {
$message = \sprintf(
static::generateMessage($message ?: 'Provided "%s" is neither greater than or equal to "%s" nor less than or equal to "%s".'),
static::stringify($value),
static::stringify($lowerLimit),
static::stringify($upperLimit)
);

throw static::createException($value, $message, static::INVALID_BETWEEN, $propertyPath, ['lower' => $lowerLimit, 'upper' => $upperLimit]);
}

return true;
}












public static function betweenExclusive($value, $lowerLimit, $upperLimit, $message = null, string $propertyPath = null): bool
{
if ($lowerLimit >= $value || $value >= $upperLimit) {
$message = \sprintf(
static::generateMessage($message ?: 'Provided "%s" is neither greater than "%s" nor less than "%s".'),
static::stringify($value),
static::stringify($lowerLimit),
static::stringify($upperLimit)
);

throw static::createException($value, $message, static::INVALID_BETWEEN_EXCLUSIVE, $propertyPath, ['lower' => $lowerLimit, 'upper' => $upperLimit]);
}

return true;
}









public static function extensionLoaded($value, $message = null, string $propertyPath = null): bool
{
if (!\extension_loaded($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Extension "%s" is required.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_EXTENSION, $propertyPath);
}

return true;
}













public static function date($value, $format, $message = null, string $propertyPath = null): bool
{
static::string($value, $message, $propertyPath);
static::string($format, $message, $propertyPath);

$dateTime = DateTime::createFromFormat('!'.$format, $value);

if (false === $dateTime || $value !== $dateTime->format($format)) {
$message = \sprintf(
static::generateMessage($message ?: 'Date "%s" is invalid or does not match format "%s".'),
static::stringify($value),
static::stringify($format)
);

throw static::createException($value, $message, static::INVALID_DATE, $propertyPath, ['format' => $format]);
}

return true;
}









public static function objectOrClass($value, $message = null, string $propertyPath = null): bool
{
if (!\is_object($value)) {
static::classExists($value, $message, $propertyPath);
}

return true;
}










public static function propertyExists($value, $property, $message = null, string $propertyPath = null): bool
{
static::objectOrClass($value);

if (!\property_exists($value, $property)) {
$message = \sprintf(
static::generateMessage($message ?: 'Class "%s" does not have property "%s".'),
static::stringify($value),
static::stringify($property)
);

throw static::createException($value, $message, static::INVALID_PROPERTY, $propertyPath, ['property' => $property]);
}

return true;
}









public static function propertiesExist($value, array $properties, $message = null, string $propertyPath = null): bool
{
static::objectOrClass($value);
static::allString($properties, $message, $propertyPath);

$invalidProperties = [];
foreach ($properties as $property) {
if (!\property_exists($value, $property)) {
$invalidProperties[] = $property;
}
}

if ($invalidProperties) {
$message = \sprintf(
static::generateMessage($message ?: 'Class "%s" does not have these properties: %s.'),
static::stringify($value),
static::stringify(\implode(', ', $invalidProperties))
);

throw static::createException($value, $message, static::INVALID_PROPERTY, $propertyPath, ['properties' => $properties]);
}

return true;
}











public static function version($version1, $operator, $version2, $message = null, string $propertyPath = null): bool
{
static::notEmpty($operator, 'versionCompare operator is required and cannot be empty.');

if (true !== \version_compare($version1, $version2, $operator)) {
$message = \sprintf(
static::generateMessage($message ?: 'Version "%s" is not "%s" version "%s".'),
static::stringify($version1),
static::stringify($operator),
static::stringify($version2)
);

throw static::createException($version1, $message, static::INVALID_VERSION, $propertyPath, ['operator' => $operator, 'version' => $version2]);
}

return true;
}










public static function phpVersion($operator, $version, $message = null, string $propertyPath = null): bool
{
static::defined('PHP_VERSION');

return static::version(PHP_VERSION, $operator, $version, $message, $propertyPath);
}











public static function extensionVersion($extension, $operator, $version, $message = null, string $propertyPath = null): bool
{
static::extensionLoaded($extension, $message, $propertyPath);

return static::version(\phpversion($extension), $operator, $version, $message, $propertyPath);
}

/**
@psalm-assert










*/
public static function isCallable($value, $message = null, string $propertyPath = null): bool
{
if (!\is_callable($value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Provided "%s" is not a callable.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_CALLABLE, $propertyPath);
}

return true;
}












public static function satisfy($value, $callback, $message = null, string $propertyPath = null): bool
{
static::isCallable($callback);

if (false === \call_user_func($callback, $value)) {
$message = \sprintf(
static::generateMessage($message ?: 'Provided "%s" is invalid according to custom rule.'),
static::stringify($value)
);

throw static::createException($value, $message, static::INVALID_SATISFY, $propertyPath);
}

return true;
}













public static function ip($value, $flag = null, $message = null, string $propertyPath = null): bool
{
static::string($value, $message, $propertyPath);
if ($flag === null) {
$filterVarResult = \filter_var($value, FILTER_VALIDATE_IP);
} else {
$filterVarResult = \filter_var($value, FILTER_VALIDATE_IP, $flag);
}
if (!$filterVarResult) {
$message = \sprintf(
static::generateMessage($message ?: 'Value "%s" was expected to be a valid IP address.'),
static::stringify($value)
);
throw static::createException($value, $message, static::INVALID_IP, $propertyPath, ['flag' => $flag]);
}

return true;
}













public static function ipv4($value, $flag = null, $message = null, string $propertyPath = null): bool
{
static::ip($value, $flag | FILTER_FLAG_IPV4, static::generateMessage($message ?: 'Value "%s" was expected to be a valid IPv4 address.'), $propertyPath);

return true;
}













public static function ipv6($value, $flag = null, $message = null, string $propertyPath = null): bool
{
static::ip($value, $flag | FILTER_FLAG_IPV6, static::generateMessage($message ?: 'Value "%s" was expected to be a valid IPv6 address.'), $propertyPath);

return true;
}







public static function defined($constant, $message = null, string $propertyPath = null): bool
{
if (!\defined($constant)) {
$message = \sprintf(static::generateMessage($message ?: 'Value "%s" expected to be a defined constant.'), $constant);

throw static::createException($constant, $message, static::INVALID_CONSTANT, $propertyPath);
}

return true;
}









public static function base64($value, $message = null, string $propertyPath = null): bool
{
if (false === \base64_decode($value, true)) {
$message = \sprintf(static::generateMessage($message ?: 'Value "%s" is not a valid base64 string.'), $value);

throw static::createException($value, $message, static::INVALID_BASE64, $propertyPath);
}

return true;
}












protected static function createException($value, $message, $code, $propertyPath = null, array $constraints = [])
{
$exceptionClass = static::$exceptionClass;

return new $exceptionClass($message, $code, $propertyPath, $value, $constraints);
}






protected static function stringify($value): string
{
$result = \gettype($value);

if (\is_bool($value)) {
$result = $value ? '<TRUE>' : '<FALSE>';
} elseif (\is_scalar($value)) {
$val = (string)$value;

if (\mb_strlen($val) > 100) {
$val = \mb_substr($val, 0, 97).'...';
}

$result = $val;
} elseif (\is_array($value)) {
$result = '<ARRAY>';
} elseif (\is_object($value)) {
$result = \get_class($value);
} elseif (\is_resource($value)) {
$result = \get_resource_type($value);
} elseif (null === $value) {
$result = '<NULL>';
}

return $result;
}






protected static function generateMessage($message): string
{
if (\is_callable($message)) {
$traces = \debug_backtrace(0);

$parameters = [];

try {
$reflection = new ReflectionClass($traces[1]['class']);
$method = $reflection->getMethod($traces[1]['function']);
foreach ($method->getParameters() as $index => $parameter) {
if ('message' !== $parameter->getName()) {
$parameters[$parameter->getName()] = \array_key_exists($index, $traces[1]['args'])
? $traces[1]['args'][$index]
: $parameter->getDefaultValue();
}
}

$parameters['::assertion'] = \sprintf('%s%s%s', $traces[1]['class'], $traces[1]['type'], $traces[1]['function']);

$message = \call_user_func_array($message, [$parameters]);
} 
catch (Throwable $exception) {
$message = \sprintf('Unable to generate message : %s', $exception->getMessage());
} 
}

return (string)$message;
}
}
