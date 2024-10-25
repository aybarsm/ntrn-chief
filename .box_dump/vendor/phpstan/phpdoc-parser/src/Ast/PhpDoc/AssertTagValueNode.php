<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;

class AssertTagValueNode implements PhpDocTagValueNode
{

use NodeAttributes;


public $type;


public $parameter;


public $isNegated;


public $isEquality;


public $description;

public function __construct(TypeNode $type, string $parameter, bool $isNegated, string $description, bool $isEquality = false)
{
$this->type = $type;
$this->parameter = $parameter;
$this->isNegated = $isNegated;
$this->isEquality = $isEquality;
$this->description = $description;
}


public function __toString(): string
{
$isNegated = $this->isNegated ? '!' : '';
$isEquality = $this->isEquality ? '=' : '';
return trim("{$isNegated}{$isEquality}{$this->type} {$this->parameter} {$this->description}");
}

}
