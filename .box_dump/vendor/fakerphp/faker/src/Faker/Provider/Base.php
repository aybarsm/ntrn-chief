<?php

namespace Faker\Provider;

use Faker\DefaultGenerator;
use Faker\Generator;
use Faker\UniqueGenerator;
use Faker\ValidGenerator;

class Base
{



protected $generator;




protected $unique;

public function __construct(Generator $generator)
{
$this->generator = $generator;
}






public static function randomDigit()
{
return mt_rand(0, 9);
}






public static function randomDigitNotNull()
{
return mt_rand(1, 9);
}








public static function randomDigitNot($except)
{
$result = self::numberBetween(0, 8);

if ($result >= $except) {
++$result;
}

return $result;
}













public static function randomNumber($nbDigits = null, $strict = false)
{
if (!is_bool($strict)) {
throw new \InvalidArgumentException('randomNumber() generates numbers of fixed width. To generate numbers between two boundaries, use numberBetween() instead.');
}

if (null === $nbDigits) {
$nbDigits = static::randomDigitNotNull();
}
$max = 10 ** $nbDigits - 1;

if ($max > mt_getrandmax()) {
throw new \InvalidArgumentException('randomNumber() can only generate numbers up to mt_getrandmax()');
}

if ($strict) {
return mt_rand(10 ** ($nbDigits - 1), $max);
}

return mt_rand(0, $max);
}












public static function randomFloat($nbMaxDecimals = null, $min = 0, $max = null)
{
if (null === $nbMaxDecimals) {
$nbMaxDecimals = static::randomDigit();
}

if (null === $max) {
$max = static::randomNumber();

if ($min > $max) {
$max = $min;
}
}

if ($min > $max) {
$tmp = $min;
$min = $max;
$max = $tmp;
}

return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $nbMaxDecimals);
}











public static function numberBetween($int1 = 0, $int2 = 2147483647)
{
$min = $int1 < $int2 ? $int1 : $int2;
$max = $int1 < $int2 ? $int2 : $int1;

return mt_rand($min, $max);
}




public static function passthrough($value)
{
return $value;
}






public static function randomLetter()
{
return chr(mt_rand(97, 122));
}






public static function randomAscii()
{
return chr(mt_rand(33, 126));
}















public static function randomElements($array = ['a', 'b', 'c'], $count = 1, $allowDuplicates = false)
{
$elements = $array;

if (is_string($array) && function_exists('enum_exists') && enum_exists($array)) {
$elements = $array::cases();
}

if ($array instanceof \Traversable) {
$elements = \iterator_to_array($array, false);
}

if (!is_array($elements)) {
throw new \InvalidArgumentException(sprintf(
'Argument for parameter $array needs to be array, an instance of %s, or an instance of %s, got %s instead.',
\UnitEnum::class,
\Traversable::class,
is_object($array) ? get_class($array) : gettype($array),
));
}

$numberOfElements = count($elements);

if (!$allowDuplicates && null !== $count && $numberOfElements < $count) {
throw new \LengthException(sprintf(
'Cannot get %d elements, only %d in array',
$count,
$numberOfElements,
));
}

if (null === $count) {
$count = mt_rand(1, $numberOfElements);
}

$randomElements = [];

$keys = array_keys($elements);
$maxIndex = $numberOfElements - 1;
$elementHasBeenSelectedAlready = [];
$numberOfRandomElements = 0;

while ($numberOfRandomElements < $count) {
$index = mt_rand(0, $maxIndex);

if (!$allowDuplicates) {
if (isset($elementHasBeenSelectedAlready[$index])) {
continue;
}

$elementHasBeenSelectedAlready[$index] = true;
}

$key = $keys[$index];

$randomElements[] = $elements[$key];

++$numberOfRandomElements;
}

return $randomElements;
}








public static function randomElement($array = ['a', 'b', 'c'])
{
$elements = $array;

if (is_string($array) && function_exists('enum_exists') && enum_exists($array)) {
$elements = $array::cases();
}

if ($array instanceof \Traversable) {
$elements = iterator_to_array($array, false);
}

if ($elements === []) {
return null;
}

if (!is_array($elements)) {
throw new \InvalidArgumentException(sprintf(
'Argument for parameter $array needs to be array, an instance of %s, or an instance of %s, got %s instead.',
\UnitEnum::class,
\Traversable::class,
is_object($array) ? get_class($array) : gettype($array),
));
}

$randomElements = static::randomElements($elements, 1);

return $randomElements[0];
}








public static function randomKey($array = [])
{
if (!$array) {
return null;
}
$keys = array_keys($array);

return $keys[mt_rand(0, count($keys) - 1)];
}
















public static function shuffle($arg = '')
{
if (is_array($arg)) {
return static::shuffleArray($arg);
}

if (is_string($arg)) {
return static::shuffleString($arg);
}

throw new \InvalidArgumentException('shuffle() only supports strings or arrays');
}

















public static function shuffleArray($array = [])
{
$shuffledArray = [];
$i = 0;
reset($array);

foreach ($array as $key => $value) {
if ($i == 0) {
$j = 0;
} else {
$j = mt_rand(0, $i);
}

if ($j == $i) {
$shuffledArray[] = $value;
} else {
$shuffledArray[] = $shuffledArray[$j];
$shuffledArray[$j] = $value;
}
++$i;
}

return $shuffledArray;
}



















public static function shuffleString($string = '', $encoding = 'UTF-8')
{
if (function_exists('mb_strlen')) {

$array = [];
$strlen = mb_strlen($string, $encoding);

for ($i = 0; $i < $strlen; ++$i) {
$array[] = mb_substr($string, $i, 1, $encoding);
}
} else {
$array = str_split($string, 1);
}

return implode('', static::shuffleArray($array));
}

private static function replaceWildcard($string, $wildcard, $callback)
{
if (($pos = strpos($string, $wildcard)) === false) {
return $string;
}

for ($i = $pos, $last = strrpos($string, $wildcard, $pos) + 1; $i < $last; ++$i) {
if ($string[$i] === $wildcard) {
$string[$i] = call_user_func($callback);
}
}

return $string;
}









public static function numerify($string = '###')
{


$toReplace = [];

if (($pos = strpos($string, '#')) !== false) {
for ($i = $pos, $last = strrpos($string, '#', $pos) + 1; $i < $last; ++$i) {
if ($string[$i] === '#') {
$toReplace[] = $i;
}
}
}

if ($nbReplacements = count($toReplace)) {
$maxAtOnce = strlen((string) mt_getrandmax()) - 1;
$numbers = '';
$i = 0;

while ($i < $nbReplacements) {
$size = min($nbReplacements - $i, $maxAtOnce);
$numbers .= str_pad(static::randomNumber($size), $size, '0', STR_PAD_LEFT);
$i += $size;
}

for ($i = 0; $i < $nbReplacements; ++$i) {
$string[$toReplace[$i]] = $numbers[$i];
}
}
$string = self::replaceWildcard($string, '%', [static::class, 'randomDigitNotNull']);

return $string;
}








public static function lexify($string = '????')
{
return self::replaceWildcard($string, '?', [static::class, 'randomLetter']);
}









public static function bothify($string = '## ??')
{
$string = self::replaceWildcard($string, '*', static function () {
return mt_rand(0, 1) === 1 ? '#' : '?';
});

return static::lexify(static::numerify($string));
}










public static function asciify($string = '****')
{
return preg_replace_callback('/\*/u', [static::class, 'randomAscii'], $string);
}




























public static function regexify($regex = '')
{

$regex = preg_replace('/^\/?\^?/', '', $regex);
$regex = preg_replace('/\$?\/?$/', '', $regex);

$regex = preg_replace('/\{(\d+)\}/', '{\1,\1}', $regex);

$regex = preg_replace('/(?<!\\\)\?/', '{0,1}', $regex);
$regex = preg_replace('/(?<!\\\)\*/', '{0,' . static::randomDigitNotNull() . '}', $regex);
$regex = preg_replace('/(?<!\\\)\+/', '{1,' . static::randomDigitNotNull() . '}', $regex);

$regex = preg_replace_callback('/(\[[^\]]+\])\{(\d+),(\d+)\}/', static function ($matches) {
return str_repeat($matches[1], Base::randomElement(range($matches[2], $matches[3])));
}, $regex);

$regex = preg_replace_callback('/(\([^\)]+\))\{(\d+),(\d+)\}/', static function ($matches) {
return str_repeat($matches[1], Base::randomElement(range($matches[2], $matches[3])));
}, $regex);

$regex = preg_replace_callback('/(\\\?.)\{(\d+),(\d+)\}/', static function ($matches) {
return str_repeat($matches[1], Base::randomElement(range($matches[2], $matches[3])));
}, $regex);

$regex = preg_replace_callback('/\((.*?)\)/', static function ($matches) {
return Base::randomElement(explode('|', str_replace(['(', ')'], '', $matches[1])));
}, $regex);

$regex = preg_replace_callback('/\[([^\]]+)\]/', static function ($matches) {
return '[' . preg_replace_callback('/(\w|\d)\-(\w|\d)/', static function ($range) {
return implode('', range($range[1], $range[2]));
}, $matches[1]) . ']';
}, $regex);

$regex = preg_replace_callback('/\[([^\]]+)\]/', static function ($matches) {

$match = preg_replace('/\\\(?!\\\)/', '', $matches[1]);
$randomElement = Base::randomElement(str_split($match));


return str_replace('.', '\.', $randomElement);
}, $regex);

$regex = preg_replace_callback('/\\\w/', [static::class, 'randomLetter'], $regex);
$regex = preg_replace_callback('/\\\d/', [static::class, 'randomDigit'], $regex);

$regex = preg_replace_callback('/(?<!\\\)\./', static function () {
$chr = static::asciify('*');

if ($chr === '\\') {
$chr .= '\\';
}

return $chr;
}, $regex);

$regex = str_replace('\\\\', '[:escaped_backslash:]', $regex);
$regex = str_replace('\\', '', $regex);
$regex = str_replace('[:escaped_backslash:]', '\\', $regex);


return $regex;
}









public static function toLower($string = '')
{
return extension_loaded('mbstring') ? mb_strtolower($string, 'UTF-8') : strtolower($string);
}









public static function toUpper($string = '')
{
return extension_loaded('mbstring') ? mb_strtoupper($string, 'UTF-8') : strtoupper($string);
}











public function optional($weight = 0.5, $default = null)
{


if ($weight > 0 && $weight < 1 && mt_rand() / mt_getrandmax() <= $weight) {
return $this->generator;
}


if (is_int($weight) && mt_rand(1, 100) <= $weight) {
return $this->generator;
}

return new DefaultGenerator($default);
}

















public function unique($reset = false, $maxRetries = 10000)
{
if ($reset || !$this->unique) {
$this->unique = new UniqueGenerator($this->generator, $maxRetries);
}

return $this->unique;
}

























public function valid($validator = null, $maxRetries = 10000)
{
return new ValidGenerator($this->generator, $validator, $maxRetries);
}
}
