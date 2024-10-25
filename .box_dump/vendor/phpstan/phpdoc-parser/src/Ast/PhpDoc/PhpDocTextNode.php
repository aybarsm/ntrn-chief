<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;

class PhpDocTextNode implements PhpDocChildNode
{

use NodeAttributes;


public $text;

public function __construct(string $text)
{
$this->text = $text;
}


public function __toString(): string
{
return $this->text;
}

}