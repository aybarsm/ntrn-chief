<?php

namespace Faker\Extension;

/**
@experimental


*/
final class Helper
{



public static function randomElement(array $array)
{
if ($array === []) {
return null;
}

return $array[array_rand($array, 1)];
}







public static function numerify(string $string): string
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
$numbers .= str_pad((string) mt_rand(0, 10 ** $size - 1), $size, '0', STR_PAD_LEFT);
$i += $size;
}

for ($i = 0; $i < $nbReplacements; ++$i) {
$string[$toReplace[$i]] = $numbers[$i];
}
}

return self::replaceWildcard($string, '%', static function () {
return mt_rand(1, 9);
});
}






public static function lexify(string $string): string
{
return self::replaceWildcard($string, '?', static function () {
return chr(mt_rand(97, 122));
});
}







public static function bothify(string $string): string
{
$string = self::replaceWildcard($string, '*', static function () {
return mt_rand(0, 1) === 1 ? '#' : '?';
});

return self::lexify(self::numerify($string));
}

private static function replaceWildcard(string $string, string $wildcard, callable $callback): string
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
}
