<?php

namespace Faker\Provider\ar_SA;

use Faker\Calculator\Luhn;

class Company extends \Faker\Provider\Company
{
protected static $formats = [
'{{lastName}} {{companySuffix}}',
'{{companyPrefix}} {{lastName}} {{companySuffix}}',
'{{companyPrefix}} {{lastName}}',
];

protected static $bsWords = [
[],
];

protected static $catchPhraseWords = [
['الخدمات', 'الحلول', 'الانظمة'],
[
'الذهبية', 'الذكية', 'المتطورة', 'المتقدمة', 'الدولية', 'المتخصصه', 'السريعة',
'المثلى', 'الابداعية', 'المتكاملة', 'المتغيرة', 'المثالية',
],
];

protected static $companyPrefix = ['شركة', 'مؤسسة', 'مجموعة', 'مكتب', 'أكاديمية', 'معرض'];

protected static $companySuffix = ['وأولاده', 'للمساهمة المحدودة', ' ذ.م.م', 'مساهمة عامة', 'وشركائه'];






public function companyPrefix()
{
return static::randomElement(self::$companyPrefix);
}




public function catchPhrase()
{
$result = [];

foreach (static::$catchPhraseWords as &$word) {
$result[] = static::randomElement($word);
}

return implode(' ', $result);
}




public function bs()
{
$result = [];

foreach (static::$bsWords as &$word) {
$result[] = static::randomElement($word);
}

return implode(' ', $result);
}




public static function companyIdNumber()
{
$partialValue = static::numerify(700 . str_repeat('#', 6));

return Luhn::generateLuhnNumber($partialValue);
}
}
