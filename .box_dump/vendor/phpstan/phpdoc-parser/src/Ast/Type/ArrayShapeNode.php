<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;

class ArrayShapeNode implements TypeNode
{

public const KIND_ARRAY = 'array';
public const KIND_LIST = 'list';
public const KIND_NON_EMPTY_ARRAY = 'non-empty-array';
public const KIND_NON_EMPTY_LIST = 'non-empty-list';

use NodeAttributes;


public $items;


public $sealed;


public $kind;


public $unsealedType;





public function __construct(
array $items,
bool $sealed = true,
string $kind = self::KIND_ARRAY,
?ArrayShapeUnsealedTypeNode $unsealedType = null
)
{
$this->items = $items;
$this->sealed = $sealed;
$this->kind = $kind;
$this->unsealedType = $unsealedType;
}


public function __toString(): string
{
$items = $this->items;

if (! $this->sealed) {
$items[] = '...' . $this->unsealedType;
}

return $this->kind . '{' . implode(', ', $items) . '}';
}

}
