<?php

namespace Faker\Provider\en_GB;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
protected static $formats = [
'+44(0)##########',
'+44(0)#### ######',
'+44(0)#########',
'+44(0)#### #####',
'0##########',
'0#########',
'0#### ######',
'0#### #####',
'0### ### ####',
'0### #######',
'(0####) ######',
'(0####) #####',
'(0###) ### ####',
'(0###) #######',
];






protected static $mobileFormats = [

'07#########',
'07### ######',
'07### ### ###',
];

protected static $e164Formats = [
'+44##########',
];






public static function mobileNumber()
{
return static::numerify(static::randomElement(static::$mobileFormats));
}
}
