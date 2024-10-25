<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Math;

use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Ramsey\Uuid\Type\NumberInterface;

/**
@psalm-immutable


*/
interface CalculatorInterface
{








public function add(NumberInterface $augend, NumberInterface ...$addends): NumberInterface;









public function subtract(NumberInterface $minuend, NumberInterface ...$subtrahends): NumberInterface;









public function multiply(NumberInterface $multiplicand, NumberInterface ...$multipliers): NumberInterface;













public function divide(
int $roundingMode,
int $scale,
NumberInterface $dividend,
NumberInterface ...$divisors
): NumberInterface;









public function fromBase(string $value, int $base): IntegerObject;









public function toBase(IntegerObject $value, int $base): string;




public function toHexadecimal(IntegerObject $value): Hexadecimal;




public function toInteger(Hexadecimal $value): IntegerObject;
}
