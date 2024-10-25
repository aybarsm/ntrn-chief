<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;

class ParamTagValueNode implements PhpDocTagValueNode
{

use NodeAttributes;


public $type;


public $isReference;


public $isVariadic;


public $parameterName;


public $description;

public function __construct(TypeNode $type, bool $isVariadic, string $parameterName, string $description, bool $isReference = false)
{
$this->type = $type;
$this->isReference = $isReference;
$this->isVariadic = $isVariadic;
$this->parameterName = $parameterName;
$this->description = $description;
}


public function __toString(): string
{
$reference = $this->isReference ? '&' : '';
$variadic = $this->isVariadic ? '...' : '';
return trim("{$this->type} {$reference}{$variadic}{$this->parameterName} {$this->description}");
}

}