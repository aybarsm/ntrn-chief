<?php

namespace Faker\Provider\hy_AM;

class Company extends \Faker\Provider\Company
{
protected static $formats = [
'{{lastName}} {{companySuffix}}',
'{{lastName}} {{companySuffix}}',
'{{lastName}} {{companySuffix}}',
'{{lastName}} {{companySuffix}}',
'{{lastName}} {{companySuffix}}',
'{{lastName}} {{companySuffix}}',
'{{lastName}} {{companySuffix}}',
'{{lastName}} {{companySuffix}}',
'{{lastName}} եղբայրներ',
];

protected static $catchPhraseWords = [

];

protected static $bsWords = [

];

protected static $companySuffix = ['ՍՊԸ', 'և որդիներ', 'ՓԲԸ', 'ԲԲԸ'];




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
}
