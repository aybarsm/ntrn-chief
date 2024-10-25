<?php

namespace Faker\Provider\fa_IR;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{



protected static $formats = [ 
'011########', 
'013########', 
'017########', 
'021########', 
'023########', 
'024########', 
'025########', 
'026########', 
'028########', 
'031########', 
'034########', 
'035########', 
'038########', 
'041########', 
'044########', 
'045########', 
'051########', 
'054########', 
'056########', 
'058########', 
'061########', 
'066########', 
'071########', 
'074########', 
'076########', 
'077########', 
'081########', 
'083########', 
'084########', 
'086########', 
'087########', 
];

protected static $mobileNumberPrefixes = [
'0910#######', 
'0911#######',
'0912#######',
'0913#######',
'0914#######',
'0915#######',
'0916#######',
'0917#######',
'0918#######',
'0919#######',
'0901#######',
'0901#######',
'0902#######',
'0903#######',
'0930#######',
'0933#######',
'0935#######',
'0936#######',
'0937#######',
'0938#######',
'0939#######',
'0920#######',
'0921#######',
'0937#######',
'0990#######', 
];

public static function mobileNumber()
{
return static::numerify(static::randomElement(static::$mobileNumberPrefixes));
}
}
