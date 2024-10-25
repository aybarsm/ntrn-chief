<?php declare(strict_types=1);

namespace PhpParser\Builder;

use PhpParser\BuilderHelpers;
use PhpParser\Node;

abstract class FunctionLike extends Declaration {
protected bool $returnByRef = false;

protected array $params = [];


protected ?Node $returnType = null;






public function makeReturnByRef() {
$this->returnByRef = true;

return $this;
}








public function addParam($param) {
$param = BuilderHelpers::normalizeNode($param);

if (!$param instanceof Node\Param) {
throw new \LogicException(sprintf('Expected parameter node, got "%s"', $param->getType()));
}

$this->params[] = $param;

return $this;
}








public function addParams(array $params) {
foreach ($params as $param) {
$this->addParam($param);
}

return $this;
}








public function setReturnType($type) {
$this->returnType = BuilderHelpers::normalizeType($type);

return $this;
}
}
