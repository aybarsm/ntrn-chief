<?php

namespace Faker\Provider\ro_RO;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
protected static $normalFormats = [
'landline' => [
'021#######', 
'023#######',
'024#######',
'025#######',
'026#######',
'027#######', 
'031#######', 
'033#######',
'034#######',
'035#######',
'036#######',
'037#######', 
],
'mobile' => [
'07########',
],
];

protected static $specialFormats = [
'toll-free' => [
'0800######',
'0801######', 
'0802######', 
'0806######', 
'0807######', 
'0870######', 
],
'premium-rate' => [
'0900######',
'0903######', 
'0906######', 
],
];




public function phoneNumber()
{
$type = static::randomElement(array_keys(static::$normalFormats));

return static::numerify(static::randomElement(static::$normalFormats[$type]));
}

public static function tollFreePhoneNumber()
{
return static::numerify(static::randomElement(static::$specialFormats['toll-free']));
}

public static function premiumRatePhoneNumber()
{
return static::numerify(static::randomElement(static::$specialFormats['premium-rate']));
}
}
