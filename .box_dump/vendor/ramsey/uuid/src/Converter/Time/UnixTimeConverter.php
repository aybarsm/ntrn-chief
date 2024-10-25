<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Converter\Time;

use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Math\CalculatorInterface;
use Ramsey\Uuid\Math\RoundingMode;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Ramsey\Uuid\Type\Time;

use function explode;
use function str_pad;

use const STR_PAD_LEFT;

/**
@psalm-immutable



*/
class UnixTimeConverter implements TimeConverterInterface
{
private const MILLISECONDS = 1000;

public function __construct(private CalculatorInterface $calculator)
{
}

public function calculateTime(string $seconds, string $microseconds): Hexadecimal
{
$timestamp = new Time($seconds, $microseconds);


$sec = $this->calculator->multiply(
$timestamp->getSeconds(),
new IntegerObject(self::MILLISECONDS),
);



$usec = $this->calculator->divide(
RoundingMode::DOWN, 
0,
$timestamp->getMicroseconds(),
new IntegerObject(self::MILLISECONDS),
);


$unixTime = $this->calculator->add($sec, $usec);

$unixTimeHex = str_pad(
$this->calculator->toHexadecimal($unixTime)->toString(),
12,
'0',
STR_PAD_LEFT
);

return new Hexadecimal($unixTimeHex);
}

public function convertTime(Hexadecimal $uuidTimestamp): Time
{
$milliseconds = $this->calculator->toInteger($uuidTimestamp);

$unixTimestamp = $this->calculator->divide(
RoundingMode::HALF_UP,
6,
$milliseconds,
new IntegerObject(self::MILLISECONDS)
);

$split = explode('.', (string) $unixTimestamp, 2);

return new Time($split[0], $split[1] ?? '0');
}
}
