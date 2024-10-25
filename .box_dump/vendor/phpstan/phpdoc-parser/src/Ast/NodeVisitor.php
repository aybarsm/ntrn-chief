<?php declare(strict_types = 1);

namespace PHPStan\PhpDocParser\Ast;







interface NodeVisitor
{












public function beforeTraverse(array $nodes): ?array;

























public function enterNode(Node $node);




















public function leaveNode(Node $node);












public function afterTraverse(array $nodes): ?array;

}
