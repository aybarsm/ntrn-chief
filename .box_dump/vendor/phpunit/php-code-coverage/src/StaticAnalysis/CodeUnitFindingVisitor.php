<?php declare(strict_types=1);








namespace SebastianBergmann\CodeCoverage\StaticAnalysis;

use function assert;
use function implode;
use function rtrim;
use function trim;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\UnionType;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use SebastianBergmann\Complexity\CyclomaticComplexityCalculatingVisitor;

/**
@psalm-type
@psalm-type
@psalm-type
@psalm-type































*/
final class CodeUnitFindingVisitor extends NodeVisitorAbstract
{
/**
@psalm-var
*/
private array $classes = [];

/**
@psalm-var
*/
private array $traits = [];

/**
@psalm-var
*/
private array $functions = [];

public function enterNode(Node $node): void
{
if ($node instanceof Class_) {
if ($node->isAnonymous()) {
return;
}

$this->processClass($node);
}

if ($node instanceof Trait_) {
$this->processTrait($node);
}

if (!$node instanceof ClassMethod && !$node instanceof Function_) {
return;
}

if ($node instanceof ClassMethod) {
$parentNode = $node->getAttribute('parent');

if ($parentNode instanceof Class_ && $parentNode->isAnonymous()) {
return;
}

$this->processMethod($node);

return;
}

$this->processFunction($node);
}

/**
@psalm-return
*/
public function classes(): array
{
return $this->classes;
}

/**
@psalm-return
*/
public function traits(): array
{
return $this->traits;
}

/**
@psalm-return
*/
public function functions(): array
{
return $this->functions;
}

private function cyclomaticComplexity(ClassMethod|Function_ $node): int
{
$nodes = $node->getStmts();

if ($nodes === null) {
return 0;
}

$traverser = new NodeTraverser;

$cyclomaticComplexityCalculatingVisitor = new CyclomaticComplexityCalculatingVisitor;

$traverser->addVisitor($cyclomaticComplexityCalculatingVisitor);


$traverser->traverse($nodes);

return $cyclomaticComplexityCalculatingVisitor->cyclomaticComplexity();
}

private function signature(ClassMethod|Function_ $node): string
{
$signature = ($node->returnsByRef() ? '&' : '') . $node->name->toString() . '(';
$parameters = [];

foreach ($node->getParams() as $parameter) {
assert(isset($parameter->var->name));

$parameterAsString = '';

if ($parameter->type !== null) {
$parameterAsString = $this->type($parameter->type) . ' ';
}

$parameterAsString .= '$' . $parameter->var->name;



$parameters[] = $parameterAsString;
}

$signature .= implode(', ', $parameters) . ')';

$returnType = $node->getReturnType();

if ($returnType !== null) {
$signature .= ': ' . $this->type($returnType);
}

return $signature;
}

private function type(ComplexType|Identifier|Name $type): string
{
if ($type instanceof NullableType) {
return '?' . $type->type;
}

if ($type instanceof UnionType) {
return $this->unionTypeAsString($type);
}

if ($type instanceof IntersectionType) {
return $this->intersectionTypeAsString($type);
}

return $type->toString();
}

private function visibility(ClassMethod $node): string
{
if ($node->isPrivate()) {
return 'private';
}

if ($node->isProtected()) {
return 'protected';
}

return 'public';
}

private function processClass(Class_ $node): void
{
$name = $node->name->toString();
$namespacedName = $node->namespacedName->toString();

$this->classes[$namespacedName] = [
'name' => $name,
'namespacedName' => $namespacedName,
'namespace' => $this->namespace($namespacedName, $name),
'startLine' => $node->getStartLine(),
'endLine' => $node->getEndLine(),
'methods' => [],
];
}

private function processTrait(Trait_ $node): void
{
$name = $node->name->toString();
$namespacedName = $node->namespacedName->toString();

$this->traits[$namespacedName] = [
'name' => $name,
'namespacedName' => $namespacedName,
'namespace' => $this->namespace($namespacedName, $name),
'startLine' => $node->getStartLine(),
'endLine' => $node->getEndLine(),
'methods' => [],
];
}

private function processMethod(ClassMethod $node): void
{
$parentNode = $node->getAttribute('parent');

if ($parentNode instanceof Interface_) {
return;
}

assert($parentNode instanceof Class_ || $parentNode instanceof Trait_ || $parentNode instanceof Enum_);
assert(isset($parentNode->name));
assert(isset($parentNode->namespacedName));
assert($parentNode->namespacedName instanceof Name);

$parentName = $parentNode->name->toString();
$parentNamespacedName = $parentNode->namespacedName->toString();

if ($parentNode instanceof Class_) {
$storage = &$this->classes;
} else {
$storage = &$this->traits;
}

if (!isset($storage[$parentNamespacedName])) {
$storage[$parentNamespacedName] = [
'name' => $parentName,
'namespacedName' => $parentNamespacedName,
'namespace' => $this->namespace($parentNamespacedName, $parentName),
'startLine' => $parentNode->getStartLine(),
'endLine' => $parentNode->getEndLine(),
'methods' => [],
];
}

$storage[$parentNamespacedName]['methods'][$node->name->toString()] = [
'methodName' => $node->name->toString(),
'signature' => $this->signature($node),
'visibility' => $this->visibility($node),
'startLine' => $node->getStartLine(),
'endLine' => $node->getEndLine(),
'ccn' => $this->cyclomaticComplexity($node),
];
}

private function processFunction(Function_ $node): void
{
assert(isset($node->name));
assert(isset($node->namespacedName));
assert($node->namespacedName instanceof Name);

$name = $node->name->toString();
$namespacedName = $node->namespacedName->toString();

$this->functions[$namespacedName] = [
'name' => $name,
'namespacedName' => $namespacedName,
'namespace' => $this->namespace($namespacedName, $name),
'signature' => $this->signature($node),
'startLine' => $node->getStartLine(),
'endLine' => $node->getEndLine(),
'ccn' => $this->cyclomaticComplexity($node),
];
}

private function namespace(string $namespacedName, string $name): string
{
return trim(rtrim($namespacedName, $name), '\\');
}

private function unionTypeAsString(UnionType $node): string
{
$types = [];

foreach ($node->types as $type) {
if ($type instanceof IntersectionType) {
$types[] = '(' . $this->intersectionTypeAsString($type) . ')';

continue;
}

$types[] = $this->typeAsString($type);
}

return implode('|', $types);
}

private function intersectionTypeAsString(IntersectionType $node): string
{
$types = [];

foreach ($node->types as $type) {
$types[] = $this->typeAsString($type);
}

return implode('&', $types);
}

private function typeAsString(Identifier|Name $node): string
{
if ($node instanceof Name) {
return $node->toCodeString();
}

return $node->toString();
}
}
