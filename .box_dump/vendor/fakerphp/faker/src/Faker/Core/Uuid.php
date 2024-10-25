<?php

namespace Faker\Core;

use Faker\Extension;

/**
@experimental
*/
final class Uuid implements Extension\UuidExtension
{
private Extension\NumberExtension $numberExtension;

public function __construct(Extension\NumberExtension $numberExtension = null)
{

$this->numberExtension = $numberExtension ?: new Number();
}

public function uuid3(): string
{


$seed = $this->numberExtension->numberBetween(0, 2147483647) . '#' . $this->numberExtension->numberBetween(0, 2147483647);


$val = md5($seed, true);
$byte = array_values(unpack('C16', $val));


$tLo = ($byte[0] << 24) | ($byte[1] << 16) | ($byte[2] << 8) | $byte[3];
$tMi = ($byte[4] << 8) | $byte[5];
$tHi = ($byte[6] << 8) | $byte[7];
$csLo = $byte[9];
$csHi = $byte[8] & 0x3f | (1 << 7);


if (pack('L', 0x6162797A) == pack('N', 0x6162797A)) {
$tLo = (($tLo & 0x000000ff) << 24) | (($tLo & 0x0000ff00) << 8)
| (($tLo & 0x00ff0000) >> 8) | (($tLo & 0xff000000) >> 24);
$tMi = (($tMi & 0x00ff) << 8) | (($tMi & 0xff00) >> 8);
$tHi = (($tHi & 0x00ff) << 8) | (($tHi & 0xff00) >> 8);
}


$tHi &= 0x0fff;
$tHi |= (3 << 12);


return sprintf(
'%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
$tLo,
$tMi,
$tHi,
$csHi,
$csLo,
$byte[10],
$byte[11],
$byte[12],
$byte[13],
$byte[14],
$byte[15],
);
}
}
