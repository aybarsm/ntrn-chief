<?php

namespace Faker\Provider\kk_KZ;

class Company extends \Faker\Provider\Company
{
protected static $companyNameFormats = [
'{{companyPrefix}} {{companyNameElement}}',
'{{companyPrefix}} {{companyNameElement}}{{companyNameElement}}',
'{{companyPrefix}} {{companyNameElement}}{{companyNameElement}}{{companyNameElement}}',
'{{companyPrefix}} {{companyNameElement}}{{companyNameElement}}{{companyNameElement}}{{companyNameSuffix}}',
];

protected static $companyPrefixes = [
'АҚ', 'ЖШС', 'ЖАҚ',
];

protected static $companyNameSuffixes = [
'Құрылыс', 'Машина', 'Бұзу', '-М', 'Лизинг', 'Страх', 'Ком', 'Телеком',
];

protected static $companyElements = [
'Қазақ', 'Кітап', 'Цемент', 'Лифт', 'Креп', 'Авто', 'Теле', 'Транс', 'Алмаз', 'Метиз',
'Мотор', 'Қаз', 'Тех', 'Сантех', 'Алматы', 'Астана', 'Электро',
];




public function company()
{
$format = static::randomElement(static::$companyNameFormats);

return $this->generator->parse($format);
}

public static function companyPrefix()
{
return static::randomElement(static::$companyPrefixes);
}

public static function companyNameElement()
{
return static::randomElement(static::$companyElements);
}

public static function companyNameSuffix()
{
return static::randomElement(static::$companyNameSuffixes);
}








public static function businessIdentificationNumber(\DateTime $registrationDate = null)
{
if (!$registrationDate) {
$registrationDate = \Faker\Provider\DateTime::dateTimeThisYear();
}

$dateAsString = $registrationDate->format('ym');
$legalEntityType = (string) self::numberBetween(4, 6);
$legalEntityAdditionalType = (string) self::numberBetween(0, 3);
$randomDigits = (string) static::numerify('######');

return $dateAsString . $legalEntityType . $legalEntityAdditionalType . $randomDigits;
}
}
