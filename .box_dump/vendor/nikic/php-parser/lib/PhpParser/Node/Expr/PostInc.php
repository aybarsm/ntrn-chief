<?php declare(strict_types=1);

namespace PhpParser\Node\Expr;

use PhpParser\Node\Expr;

class PostInc extends Expr {

public Expr $var;







public function __construct(Expr $var, array $attributes = []) {
$this->attributes = $attributes;
$this->var = $var;
}

public function getSubNodeNames(): array {
return ['var'];
}

public function getType(): string {
return 'Expr_PostInc';
}
}
