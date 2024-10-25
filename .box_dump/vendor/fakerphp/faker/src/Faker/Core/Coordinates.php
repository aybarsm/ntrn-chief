<?php

declare(strict_types=1);

namespace Faker\Core;

use Faker\Extension;

/**
@experimental
*/
final class Coordinates implements Extension\Extension
{
private Extension\NumberExtension $numberExtension;

public function __construct(Extension\NumberExtension $numberExtension = null)
{
$this->numberExtension = $numberExtension ?: new Number();
}






public function latitude(float $min = -90.0, float $max = 90.0): float
{
if ($min < -90 || $max < -90) {
throw new \LogicException('Latitude cannot be less that -90.0');
}

if ($min > 90 || $max > 90) {
throw new \LogicException('Latitude cannot be greater that 90.0');
}

return $this->randomFloat(6, $min, $max);
}






public function longitude(float $min = -180.0, float $max = 180.0): float
{
if ($min < -180 || $max < -180) {
throw new \LogicException('Longitude cannot be less that -180.0');
}

if ($min > 180 || $max > 180) {
throw new \LogicException('Longitude cannot be greater that 180.0');
}

return $this->randomFloat(6, $min, $max);
}






public function localCoordinates(): array
{
return [
'latitude' => $this->latitude(),
'longitude' => $this->longitude(),
];
}

private function randomFloat(int $nbMaxDecimals, float $min, float $max): float
{
if ($min > $max) {
throw new \LogicException('Invalid coordinates boundaries');
}

return $this->numberExtension->randomFloat($nbMaxDecimals, $min, $max);
}
}
