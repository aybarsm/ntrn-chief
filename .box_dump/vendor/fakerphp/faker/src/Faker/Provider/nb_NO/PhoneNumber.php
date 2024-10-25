<?php

namespace Faker\Provider\nb_NO;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{



protected static $formats = [
'+47#########',
'+47 ## ## ## ##',
'## ## ## ##',
'## ## ## ##',
'########',
'########',
'9## ## ###',
'4## ## ###',
'9#######',
'4#######',
];




protected static $mobileFormats = [
'+474#######',
'+479#######',
'9## ## ###',
'4## ## ###',
'9#######',
'4#######',
];

public function mobileNumber()
{
$format = static::randomElement(static::$mobileFormats);

return self::numerify($this->generator->parse($format));
}
}
