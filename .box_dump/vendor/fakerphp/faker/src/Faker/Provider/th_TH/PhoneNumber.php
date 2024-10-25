<?php

namespace Faker\Provider\th_TH;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{





protected static $formats = [
'0 #### ####',
'+66 #### ####',
'0########',
];




protected static $mobileFormats = [
'08# ### ####',
'08 #### ####',
'09# ### ####',
'09 #### ####',
'06# ### ####',
'06 #### ####',
];






public static function mobileNumber()
{
return static::numerify(static::randomElement(static::$mobileFormats));
}
}
