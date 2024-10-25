<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;

class PropertyTagValueNode implements PhpDocTagValueNode
{

use NodeAttributes;


public $type;


public $propertyName;


public $description;

public function __construct(TypeNode $type, string $propertyName, string $description)
{
$this->type = $type;
$this->propertyName = $propertyName;
$this->description = $description;
}


public function __toString(): string
{
return trim("{$this->type} {$this->propertyName} {$this->description}");
}

}
