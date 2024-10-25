<?php

declare(strict_types=1);

namespace Faker\Core;

use Faker\Extension;

/**
@experimental
*/
final class Blood implements Extension\BloodExtension
{



private array $bloodTypes = ['A', 'AB', 'B', 'O'];




private array $bloodRhFactors = ['+', '-'];

public function bloodType(): string
{
return Extension\Helper::randomElement($this->bloodTypes);
}

public function bloodRh(): string
{
return Extension\Helper::randomElement($this->bloodRhFactors);
}

public function bloodGroup(): string
{
return sprintf(
'%s%s',
$this->bloodType(),
$this->bloodRh(),
);
}
}
