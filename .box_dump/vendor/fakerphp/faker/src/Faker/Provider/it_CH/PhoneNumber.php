<?php

namespace Faker\Provider\it_CH;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
protected static $formats = [
'+41 (0)## ### ## ##',
'+41(0)#########',
'+41 ## ### ## ##',
'0#########',
'0## ### ## ##',
];






protected static $mobileFormats = [

'075 ### ## ##',
'075#######',
'076 ### ## ##',
'076#######',
'077 ### ## ##',
'077#######',
'078 ### ## ##',
'078#######',
'079 ### ## ##',
'079#######',
];






public static function mobileNumber()
{
return static::numerify(static::randomElement(static::$mobileFormats));
}
}
