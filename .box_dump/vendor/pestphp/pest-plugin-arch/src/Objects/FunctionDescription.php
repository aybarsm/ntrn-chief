<?php

declare(strict_types=1);

namespace Pest\Arch\Objects;

use PHPUnit\Architecture\Asserts\Dependencies\Elements\ObjectUses;
use PHPUnit\Architecture\Elements\ObjectDescription;
use ReflectionFunction;




final class FunctionDescription extends ObjectDescription 
{



public static function make(string $path): self
{
$description = new self();

$description->path = $path;


$description->name = $path;
$description->uses = new ObjectUses([]);


return $description;
}
}
