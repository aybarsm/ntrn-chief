<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;

class GenericTagValueNode implements PhpDocTagValueNode
{

use NodeAttributes;


public $value;

public function __construct(string $value)
{
$this->value = $value;
}


public function __toString(): string
{
return $this->value;
}

}
