<?php

namespace Faker\Provider\pt_BR;

require_once 'check_digit.php';

class Company extends \Faker\Provider\Company
{
protected static $formats = [
'{{lastName}} {{companySuffix}}',
'{{lastName}}-{{lastName}}',
'{{lastName}} e {{lastName}}',
'{{lastName}} e {{lastName}} {{companySuffix}}',
'{{lastName}} Comercial Ltda.',
];

protected static $companySuffix = ['e Filhos', 'e Associados', 'Ltda.', 'S.A.'];










public function cnpj($formatted = true)
{
$n = $this->generator->numerify('########0001');
$n .= check_digit($n);
$n .= check_digit($n);

return $formatted ? vsprintf('%d%d.%d%d%d.%d%d%d/%d%d%d%d-%d%d', str_split($n)) : $n;
}
}
