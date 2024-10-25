<?php

namespace Faker\Provider\pt_BR;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
protected static $landlineFormats = ['2###-####', '3###-####', '4###-####'];






protected static $cellphoneFormats = ['9####-####'];








public static function areaCode()
{
$areaCodes = [
'11', '12', '13', '14', '15', '16', '17', '18', '19', '21', '22', '24',
'27', '28', '31', '32', '33', '34', '35', '37', '38', '41', '42', '43',
'44', '45', '46', '47', '48', '49', '51', '53', '54', '55', '61', '62',
'63', '64', '65', '66', '67', '68', '69', '71', '73', '74', '75', '77',
'79', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92',
'93', '94', '95', '96', '97', '98', '99',
];

return self::randomElement($areaCodes);
}








public static function cellphone($formatted = true)
{
$number = static::numerify(static::randomElement(static::$cellphoneFormats));

if (!$formatted) {
$number = strtr($number, ['-' => '']);
}

return $number;
}








public static function landline($formatted = true)
{
$number = static::numerify(static::randomElement(static::$landlineFormats));

if (!$formatted) {
$number = strtr($number, ['-' => '']);
}

return $number;
}






public static function phone($formatted = true)
{
$options = static::randomElement([
['cellphone', false],
['cellphone', true],
['landline', null],
]);

return call_user_func([static::class, $options[0]], $formatted, $options[1]);
}









protected static function anyPhoneNumber($type, $formatted = true)
{
$area = static::areaCode();
$number = ($type == 'cellphone') ?
static::cellphone($formatted) :
static::landline($formatted);

return $formatted ? "($area) $number" : $area . $number;
}








public static function cellphoneNumber($formatted = true)
{
return static::anyPhoneNumber('cellphone', $formatted);
}








public static function landlineNumber($formatted = true)
{
return static::anyPhoneNumber('landline', $formatted);
}




public function phoneNumber()
{
$method = static::randomElement(['cellphoneNumber', 'landlineNumber']);

return call_user_func([static::class, $method], true);
}




public static function phoneNumberCleared()
{
$method = static::randomElement(['cellphoneNumber', 'landlineNumber']);

return call_user_func([static::class, $method], false);
}
}
