<?php declare(strict_types=1);

namespace PhpParser\Builder;

use PhpParser\Builder;
use PhpParser\BuilderHelpers;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Stmt;

class TraitUseAdaptation implements Builder {
private const TYPE_UNDEFINED = 0;
private const TYPE_ALIAS = 1;
private const TYPE_PRECEDENCE = 2;

protected int $type;
protected ?Node\Name $trait;
protected Node\Identifier $method;
protected ?int $modifier = null;
protected ?Node\Identifier $alias = null;

protected array $insteadof = [];







public function __construct($trait, $method) {
$this->type = self::TYPE_UNDEFINED;

$this->trait = is_null($trait) ? null : BuilderHelpers::normalizeName($trait);
$this->method = BuilderHelpers::normalizeIdentifier($method);
}








public function as($alias) {
if ($this->type === self::TYPE_UNDEFINED) {
$this->type = self::TYPE_ALIAS;
}

if ($this->type !== self::TYPE_ALIAS) {
throw new \LogicException('Cannot set alias for not alias adaptation buider');
}

$this->alias = BuilderHelpers::normalizeIdentifier($alias);
return $this;
}






public function makePublic() {
$this->setModifier(Modifiers::PUBLIC);
return $this;
}






public function makeProtected() {
$this->setModifier(Modifiers::PROTECTED);
return $this;
}






public function makePrivate() {
$this->setModifier(Modifiers::PRIVATE);
return $this;
}








public function insteadof(...$traits) {
if ($this->type === self::TYPE_UNDEFINED) {
if (is_null($this->trait)) {
throw new \LogicException('Precedence adaptation must have trait');
}

$this->type = self::TYPE_PRECEDENCE;
}

if ($this->type !== self::TYPE_PRECEDENCE) {
throw new \LogicException('Cannot add overwritten traits for not precedence adaptation buider');
}

foreach ($traits as $trait) {
$this->insteadof[] = BuilderHelpers::normalizeName($trait);
}

return $this;
}

protected function setModifier(int $modifier): void {
if ($this->type === self::TYPE_UNDEFINED) {
$this->type = self::TYPE_ALIAS;
}

if ($this->type !== self::TYPE_ALIAS) {
throw new \LogicException('Cannot set access modifier for not alias adaptation buider');
}

if (is_null($this->modifier)) {
$this->modifier = $modifier;
} else {
throw new \LogicException('Multiple access type modifiers are not allowed');
}
}






public function getNode(): Node {
switch ($this->type) {
case self::TYPE_ALIAS:
return new Stmt\TraitUseAdaptation\Alias($this->trait, $this->method, $this->modifier, $this->alias);
case self::TYPE_PRECEDENCE:
return new Stmt\TraitUseAdaptation\Precedence($this->trait, $this->method, $this->insteadof);
default:
throw new \LogicException('Type of adaptation is not defined');
}
}
}