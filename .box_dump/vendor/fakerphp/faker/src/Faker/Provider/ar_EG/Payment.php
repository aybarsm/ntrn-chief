<?php

namespace Faker\Provider\ar_EG;

class Payment extends \Faker\Provider\Payment
{





public function bankAccountNumber(): string
{
return self::iban('EG', '', 25);
}
}
