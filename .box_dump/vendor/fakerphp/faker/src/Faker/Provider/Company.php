<?php

namespace Faker\Provider;

class Company extends Base
{
protected static $formats = [
'{{lastName}} {{companySuffix}}',
];

protected static $companySuffix = ['Ltd'];

protected static $jobTitleFormat = [
'{{word}}',
];






public function company()
{
$format = static::randomElement(static::$formats);

return $this->generator->parse($format);
}






public static function companySuffix()
{
return static::randomElement(static::$companySuffix);
}






public function jobTitle()
{
$format = static::randomElement(static::$jobTitleFormat);

return $this->generator->parse($format);
}
}
