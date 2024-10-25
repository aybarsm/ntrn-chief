<?php

namespace Faker\Provider;

class Medical extends Base
{
protected static $bloodTypes = ['A', 'AB', 'B', 'O'];

protected static $bloodRhFactors = ['+', '-'];




public static function bloodType(): string
{
return static::randomElement(static::$bloodTypes);
}




public static function bloodRh(): string
{
return static::randomElement(static::$bloodRhFactors);
}




public function bloodGroup(): string
{
return $this->generator->parse('{{bloodType}}{{bloodRh}}');
}
}
