<?php

declare(strict_types=1);










namespace Carbon\PHPStan;

use Carbon\CarbonInterface;
use Carbon\FactoryImmutable;
use Closure;
use InvalidArgumentException;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ClosureTypeFactory;
use ReflectionFunction;
use ReflectionMethod;
use stdClass;
use Throwable;






final class MacroExtension implements MethodsClassReflectionExtension
{



protected $reflectionProvider;




protected $closureTypeFactory;







public function __construct(
ReflectionProvider $reflectionProvider,
ClosureTypeFactory $closureTypeFactory
) {
$this->reflectionProvider = $reflectionProvider;
$this->closureTypeFactory = $closureTypeFactory;
}




public function hasMethod(ClassReflection $classReflection, string $methodName): bool
{
if (
$classReflection->getName() !== CarbonInterface::class &&
!$classReflection->isSubclassOf(CarbonInterface::class)
) {
return false;
}

$className = $classReflection->getName();

return \is_callable([$className, 'hasMacro']) &&
$className::hasMacro($methodName);
}




public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
{
$macros = FactoryImmutable::getDefaultInstance()->getSettings()['macros'] ?? [];
$macro = $macros[$methodName] ?? throw new InvalidArgumentException("Macro '$methodName' not found");
$static = false;
$final = false;
$deprecated = false;
$docComment = null;

if (\is_array($macro) && \count($macro) === 2 && \is_string($macro[1])) {
\assert($macro[1] !== '');

$reflection = new ReflectionMethod($macro[0], $macro[1]);
$closure = \is_object($macro[0]) ? $reflection->getClosure($macro[0]) : $reflection->getClosure();

$static = $reflection->isStatic();
$final = $reflection->isFinal();
$deprecated = $reflection->isDeprecated();
$docComment = $reflection->getDocComment() ?: null;
} elseif (\is_string($macro)) {
$reflection = new ReflectionFunction($macro);
$closure = $reflection->getClosure();
$deprecated = $reflection->isDeprecated();
$docComment = $reflection->getDocComment() ?: null;
} elseif ($macro instanceof Closure) {
$closure = $macro;

try {
$boundClosure = Closure::bind($closure, new stdClass());
$static = (!$boundClosure || (new ReflectionFunction($boundClosure))->getClosureThis() === null);
} catch (Throwable) {
$static = true;
}

$reflection = new ReflectionFunction($macro);
$deprecated = $reflection->isDeprecated();
$docComment = $reflection->getDocComment() ?: null;
}

if (!isset($closure)) {
throw new InvalidArgumentException('Could not create reflection from the spec given'); 
}

$closureType = $this->closureTypeFactory->fromClosureObject($closure);

return new MacroMethodReflection(
$classReflection,
$methodName,
$closureType,
$static,
$final,
$deprecated,
$docComment,
);
}
}