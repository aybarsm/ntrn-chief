<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\Util;

use function sprintf;




final class Percentage
{
private readonly float $fraction;
private readonly float $total;

public static function fromFractionAndTotal(float $fraction, float $total): self
{
return new self($fraction, $total);
}

private function __construct(float $fraction, float $total)
{
$this->fraction = $fraction;
$this->total = $total;
}

public function asFloat(): float
{
if ($this->total > 0) {
return ($this->fraction / $this->total) * 100;
}

return 100.0;
}

public function asString(): string
{
if ($this->total > 0) {
return sprintf('%01.2F%%', $this->asFloat());
}

return '';
}

public function asFixedWidthString(): string
{
if ($this->total > 0) {
return sprintf('%6.2F%%', $this->asFloat());
}

return '';
}
}
