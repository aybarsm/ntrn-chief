<?php

namespace Faker\Provider\is_IS;

class Company extends \Faker\Provider\Company
{



protected static $formats = [
'{{lastName}} {{companySuffix}}',
'{{lastName}} {{companySuffix}}',
'{{lastName}} {{companySuffix}}',
'{{firstname}} {{lastName}} {{companySuffix}}',
'{{middleName}} {{companySuffix}}',
'{{middleName}} {{companySuffix}}',
'{{middleName}} {{companySuffix}}',
'{{firstname}} {{middleName}} {{companySuffix}}',
'{{lastName}} & {{lastName}} {{companySuffix}}',
'{{lastName}} og {{lastName}} {{companySuffix}}',
'{{lastName}} & {{lastName}} {{companySuffix}}',
'{{lastName}} og {{lastName}} {{companySuffix}}',
'{{middleName}} & {{middleName}} {{companySuffix}}',
'{{middleName}} og {{middleName}} {{companySuffix}}',
'{{middleName}} & {{lastName}}',
'{{middleName}} og {{lastName}}',
];




protected static $companySuffix = ['ehf.', 'hf.', 'sf.'];






protected static $vskFormat = '%####';






public static function vsk()
{
return static::numerify(static::$vskFormat);
}
}
