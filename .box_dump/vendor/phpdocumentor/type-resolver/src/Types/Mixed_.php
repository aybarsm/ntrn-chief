<?php

declare(strict_types=1);










namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;

/**
@psalm-immutable


*/
final class Mixed_ implements Type
{



public function __toString(): string
{
return 'mixed';
}
}
