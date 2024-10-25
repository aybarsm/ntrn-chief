<?php declare(strict_types=1);

namespace PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function array_pop;
use function count;







final class ParentConnectingVisitor extends NodeVisitorAbstract {



private array $stack = [];

public function beforeTraverse(array $nodes) {
$this->stack = [];
}

public function enterNode(Node $node) {
if (!empty($this->stack)) {
$node->setAttribute('parent', $this->stack[count($this->stack) - 1]);
}

$this->stack[] = $node;
}

public function leaveNode(Node $node) {
array_pop($this->stack);
}
}
