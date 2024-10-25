<?php declare(strict_types=1);

namespace PhpParser\Node;

use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeAbstract;

class PropertyHook extends NodeAbstract implements FunctionLike {

public array $attrGroups;

public int $flags;

public bool $byRef;

public Identifier $name;

public array $params;

public $body;

















public function __construct($name, $body, array $subNodes = [], array $attributes = []) {
$this->attributes = $attributes;
$this->name = \is_string($name) ? new Identifier($name) : $name;
$this->body = $body;
$this->flags = $subNodes['flags'] ?? 0;
$this->byRef = $subNodes['byRef'] ?? false;
$this->params = $subNodes['params'] ?? [];
$this->attrGroups = $subNodes['attrGroups'] ?? [];
}

public function returnsByRef(): bool {
return $this->byRef;
}

public function getParams(): array {
return $this->params;
}

public function getReturnType() {
return null;
}

public function getStmts(): ?array {
if ($this->body instanceof Expr) {
return [new Return_($this->body)];
}
return $this->body;
}

public function getAttrGroups(): array {
return $this->attrGroups;
}

public function getType(): string {
return 'PropertyHook';
}

public function getSubNodeNames(): array {
return ['attrGroups', 'flags', 'byRef', 'name', 'params', 'body'];
}
}
