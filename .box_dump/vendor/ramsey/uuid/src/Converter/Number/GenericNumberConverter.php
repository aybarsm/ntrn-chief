<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Converter\Number;

use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Math\CalculatorInterface;
use Ramsey\Uuid\Type\Integer as IntegerObject;

/**
@psalm-immutable



*/
class GenericNumberConverter implements NumberConverterInterface
{
public function __construct(private CalculatorInterface $calculator)
{
}

/**
@psalm-pure
@psalm-return
@psalm-suppress
@psalm-suppress

*/
public function fromHex(string $hex): string
{
return $this->calculator->fromBase($hex, 16)->toString();
}

/**
@psalm-pure
@psalm-return
@psalm-suppress
@psalm-suppress

*/
public function toHex(string $number): string
{
/**
@phpstan-ignore-next-line */
return $this->calculator->toBase(new IntegerObject($number), 16);
}
}
