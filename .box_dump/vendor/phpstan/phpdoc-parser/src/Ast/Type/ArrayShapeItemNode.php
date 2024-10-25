<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;

class ArrayShapeItemNode implements TypeNode
{

use NodeAttributes;


public $keyName;


public $optional;


public $valueType;




public function __construct($keyName, bool $optional, TypeNode $valueType)
{
$this->keyName = $keyName;
$this->optional = $optional;
$this->valueType = $valueType;
}


public function __toString(): string
{
if ($this->keyName !== null) {
return sprintf(
'%s%s: %s',
(string) $this->keyName,
$this->optional ? '?' : '',
(string) $this->valueType
);
}

return (string) $this->valueType;
}

}
