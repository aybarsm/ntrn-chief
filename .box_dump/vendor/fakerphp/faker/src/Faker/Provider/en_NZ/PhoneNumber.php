<?php

namespace Faker\Provider\en_NZ;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{





protected static $formats = [

'{{areaCode}}{{beginningNumber}}######',
'{{areaCode}} {{beginningNumber}}## ####',
];






protected static $mobileFormats = [

'02########',
'02#########',
'02# ### ####',
'02# #### ####',
];






protected static $tollFreeFormats = [
'0508######',
'0508 ######',
'0508 ### ###',
'0800######',
'0800 ######',
'0800 ### ###',
];






protected static $areaCodes = [
'02', '03', '04', '06', '07', '09',
];






protected static $beginningNumbers = [
'2', '3', '4', '5', '6', '7', '8', '9',
];






public static function mobileNumber()
{
return static::numerify(static::randomElement(static::$mobileFormats));
}






public static function tollFreeNumber()
{
return static::numerify(static::randomElement(static::$tollFreeFormats));
}






public static function areaCode()
{
return static::numerify(static::randomElement(static::$areaCodes));
}






public static function beginningNumber()
{
return static::numerify(static::randomElement(static::$beginningNumbers));
}
}
