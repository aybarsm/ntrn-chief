<?php

namespace Faker\Provider\el_CY;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
protected static $formats = [
'+3572#######',
'+3579#######',
'2#######',
'9#######',
];






protected static $mobileFormats = [
'9#######',
];






public static function mobileNumber()
{
return static::numerify(static::randomElement(static::$mobileFormats));
}
}
