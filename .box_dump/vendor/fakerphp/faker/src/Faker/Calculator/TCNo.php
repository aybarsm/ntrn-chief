<?php

namespace Faker\Calculator;





class TCNo
{













public static function checksum($identityPrefix)
{
return \Faker\Provider\tr_TR\Person::tcNoChecksum($identityPrefix);
}











public static function isValid($tcNo)
{
return \Faker\Provider\tr_TR\Person::tcNoIsValid($tcNo);
}
}
