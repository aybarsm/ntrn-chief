<?php

namespace Faker\Provider\ko_KR;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{


protected static $formats = [

'070-####-####',
'02-####-####',
'03#-####-####',
'04#-####-####',
'05#-####-####',
'06#-####-####',


'010-####-####',


'15##-####',
'16##-####',
'18##-####',
];

public function localAreaPhoneNumber()
{
$format = self::randomElement(array_slice(static::$formats, 0, 6));

return self::numerify($this->generator->parse($format));
}

public function cellPhoneNumber()
{
$format = self::randomElement(array_slice(static::$formats, 6, 1));

return self::numerify($this->generator->parse($format));
}
}
