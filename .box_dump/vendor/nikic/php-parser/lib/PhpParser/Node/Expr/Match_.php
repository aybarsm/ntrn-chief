<?php declare(strict_types=1);

namespace PhpParser\Node\Expr;

use PhpParser\Node;
use PhpParser\Node\MatchArm;

class Match_ extends Node\Expr {

public Node\Expr $cond;

public array $arms;






public function __construct(Node\Expr $cond, array $arms = [], array $attributes = []) {
$this->attributes = $attributes;
$this->cond = $cond;
$this->arms = $arms;
}

public function getSubNodeNames(): array {
return ['cond', 'arms'];
}

public function getType(): string {
return 'Expr_Match';
}
}
