<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Provider\Node;

use Ramsey\Collection\AbstractCollection;
use Ramsey\Uuid\Provider\NodeProviderInterface;
use Ramsey\Uuid\Type\Hexadecimal;

/**
@extends







*/
class NodeProviderCollection extends AbstractCollection
{
public function getType(): string
{
return NodeProviderInterface::class;
}

/**
@phpcsSuppress
@psalm-suppress





*/
public function unserialize($serialized): void
{

$data = unserialize($serialized, [
'allowed_classes' => [
Hexadecimal::class,
RandomNodeProvider::class,
StaticNodeProvider::class,
SystemNodeProvider::class,
],
]);

$this->data = array_filter(
$data,
function ($unserialized): bool {
return $unserialized instanceof NodeProviderInterface;
}
);
}
}
