<?php

namespace Faker\Extension;

/**
@experimental
*/
interface NumberExtension extends Extension
{








public function numberBetween(int $min, int $max): int;




public function randomDigit(): int;




public function randomDigitNot(int $except): int;




public function randomDigitNotZero(): int;






public function randomFloat(?int $nbMaxDecimals, float $min, ?float $max): float;











public function randomNumber(?int $nbDigits, bool $strict): int;
}
