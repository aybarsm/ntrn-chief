<?php

namespace Faker\Provider\sv_SE;




class PhoneNumber extends \Faker\Provider\PhoneNumber
{



protected static $formats = [
'08-### ### ##',
'0%#-### ## ##',
'0%########',
'+46 (0)%## ### ###',
'+46(0)%########',
'+46 %## ### ###',
'+46%########',

'08-### ## ##',
'0%#-## ## ##',
'0%##-### ##',
'0%#######',
'+46 (0)8 ### ## ##',
'+46 (0)%# ## ## ##',
'+46 (0)%## ### ##',
'+46 (0)%#######',
'+46(0)%#######',
'+46%#######',

'08-## ## ##',
'0%#-### ###',
'0%#######',
'+46 (0)%######',
'+46(0)%######',
'+46%######',
];




protected static array $mobileFormats = [
'+467########',
'+46(0)7########',
'+46 (0)7## ## ## ##',
'+46 (0)7## ### ###',
'07## ## ## ##',
'07## ### ###',
'07##-## ## ##',
'07##-### ###',
'07# ### ## ##',
'07#-### ## ##',
'07#-#######',
];

public function mobileNumber(): string
{
$format = static::randomElement(static::$mobileFormats);

return self::numerify($this->generator->parse($format));
}
}
