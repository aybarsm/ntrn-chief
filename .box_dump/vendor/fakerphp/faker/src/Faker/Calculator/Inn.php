<?php

namespace Faker\Calculator;





class Inn
{












public static function checksum($inn)
{
return \Faker\Provider\ru_RU\Company::inn10Checksum($inn);
}











public static function isValid($inn)
{
return \Faker\Provider\ru_RU\Company::inn10IsValid($inn);
}
}
