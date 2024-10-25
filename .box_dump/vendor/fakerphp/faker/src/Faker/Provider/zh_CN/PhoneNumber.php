<?php

namespace Faker\Provider\zh_CN;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
protected static $operators = [
134, 135, 136, 137, 138, 139, 147, 150, 151, 152, 157, 158, 159, 1705, 178, 182, 183, 184, 187, 188, 
130, 131, 132, 145, 155, 156, 1707, 1708, 1709, 1718, 1719, 176, 185, 186, 
133, 153, 1700, 1701, 177, 180, 181, 189, 
170, 171, 
];

protected static $formats = ['###########'];

public function phoneNumber()
{
$operator = static::randomElement(static::$operators);
$format = static::randomElement(static::$formats);

return $operator . static::numerify(substr($format, 0, strlen($format) - strlen($operator)));
}
}
