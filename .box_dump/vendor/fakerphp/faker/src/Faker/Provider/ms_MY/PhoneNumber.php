<?php

namespace Faker\Provider\ms_MY;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
protected static $formats = [
'{{mobileNumber}}',
'{{fixedLineNumber}}',
'{{voipNumber}}',
];

protected static $plusSymbol = [
'+',
];

protected static $countryCodePrefix = [
'6',
];




protected static $zeroOneOnePrefix = ['10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '22', '23', '32'];
protected static $zeroOneFourPrefix = ['2', '3', '4', '5', '6', '7', '8', '9'];
protected static $zeroOneFivePrefix = ['1', '2', '3', '4', '5', '6', '9'];




protected static $mobileNumberFormatsWithFormatting = [
'010-### ####',
'011-{{zeroOneOnePrefix}}## ####',
'012-### ####',
'013-### ####',
'014-{{zeroOneFourPrefix}}## ####',
'016-### ####',
'017-### ####',
'018-### ####',
'019-### ####',
];

protected static $mobileNumberFormats = [
'010#######',
'011{{zeroOneOnePrefix}}######',
'012#######',
'013#######',
'014{{zeroOneFourPrefix}}######',
'016#######',
'017#######',
'018#######',
'019#######',
];




protected static $fixedLineNumberFormatsWithFormatting = [
'03-#### ####',
'04-### ####',
'05-### ####',
'06-### ####',
'07-### ####',
'08#-## ####',
'09-### ####',
];

protected static $fixedLineNumberFormats = [
'03########',
'04#######',
'05#######',
'06#######',
'07#######',
'08#######',
'09#######',
];




protected static $voipNumberWithFormatting = [
'015-{{zeroOneFivePrefix}}## ####',
];

protected static $voipNumber = [
'015{{zeroOneFivePrefix}}######',
];











public function mobileNumber($countryCodePrefix = true, $formatting = true)
{
if ($formatting) {
$format = static::randomElement(static::$mobileNumberFormatsWithFormatting);
} else {
$format = static::randomElement(static::$mobileNumberFormats);
}

if ($countryCodePrefix) {
return static::countryCodePrefix($formatting) . static::numerify($this->generator->parse($format));
}

return static::numerify($this->generator->parse($format));
}








public static function zeroOneOnePrefix()
{
return static::numerify(static::randomElement(static::$zeroOneOnePrefix));
}








public static function zeroOneFourPrefix()
{
return static::numerify(static::randomElement(static::$zeroOneFourPrefix));
}








public static function zeroOneFivePrefix()
{
return static::numerify(static::randomElement(static::$zeroOneFivePrefix));
}











public function fixedLineNumber($countryCodePrefix = true, $formatting = true)
{
if ($formatting) {
$format = static::randomElement(static::$fixedLineNumberFormatsWithFormatting);
} else {
$format = static::randomElement(static::$fixedLineNumberFormats);
}

if ($countryCodePrefix) {
return static::countryCodePrefix($formatting) . static::numerify($this->generator->parse($format));
}

return static::numerify($this->generator->parse($format));
}











public function voipNumber($countryCodePrefix = true, $formatting = true)
{
if ($formatting) {
$format = static::randomElement(static::$voipNumberWithFormatting);
} else {
$format = static::randomElement(static::$voipNumber);
}

if ($countryCodePrefix) {
return static::countryCodePrefix($formatting) . static::numerify($this->generator->parse($format));
}

return static::numerify($this->generator->parse($format));
}










public static function countryCodePrefix($formatting = true)
{
if ($formatting) {
return static::randomElement(static::$plusSymbol) . static::randomElement(static::$countryCodePrefix);
}

return static::randomElement(static::$countryCodePrefix);
}
}
