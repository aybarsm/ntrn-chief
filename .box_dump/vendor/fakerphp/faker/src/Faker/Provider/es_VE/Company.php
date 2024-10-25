<?php

namespace Faker\Provider\es_VE;

class Company extends \Faker\Provider\Company
{
protected static $formats = [
'{{companyPrefix}} {{lastName}} {{companySuffix}}',
'{{companyPrefix}} {{lastName}}',
'{{companyPrefix}} {{lastName}} y {{lastName}}',
'{{lastName}} y {{lastName}} {{companySuffix}}',
'{{lastName}} de {{lastName}} {{companySuffix}}',
'{{lastName}} y {{lastName}}',
'{{lastName}} de {{lastName}}',
];

protected static $companyPrefix = [
'Asociación', 'Centro', 'Corporación', 'Cooperativa', 'Empresa', 'Gestora', 'Global', 'Grupo', 'Viajes',
'Inversiones', 'Lic.', 'Dr.',
];
protected static $companySuffix = ['S.R.L.', 'C.A.', 'S.A.', 'R.L.', 'etc'];




public static function companyPrefix()
{
return static::randomElement(static::$companyPrefix);
}








public function taxpayerIdentificationNumber($separator = '')
{
return static::randomElement(['J', 'G', 'V', 'E', 'P', 'C']) . $separator . static::numerify('########') . $separator . static::numerify('#');
}
}
