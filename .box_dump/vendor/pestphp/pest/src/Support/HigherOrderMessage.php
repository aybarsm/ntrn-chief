<?php

declare(strict_types=1);

namespace Pest\Support;

use Closure;
use ReflectionClass;
use Throwable;




final class HigherOrderMessage
{
public const UNDEFINED_METHOD = 'Method %s does not exist';






public ?Closure $condition = null;






public function __construct(
public string $filename,
public int $line,
public string $name,
public ?array $arguments
) {

}

/**
@template




*/
public function call(object $target): mixed
{
if (is_callable($this->condition) && call_user_func(Closure::bind($this->condition, $target)) === false) {
return $target;
}

if ($this->hasHigherOrderCallable()) {

return (new HigherOrderCallables($target))->{$this->name}(...$this->arguments);
}

try {
return is_array($this->arguments)
? Reflection::call($target, $this->name, $this->arguments)
: $target->{$this->name}; 
} catch (Throwable $throwable) {
Reflection::setPropertyValue($throwable, 'file', $this->filename);
Reflection::setPropertyValue($throwable, 'line', $this->line);

if ($throwable->getMessage() === $this->getUndefinedMethodMessage($target, $this->name)) {

$reflection = new ReflectionClass($target);

$reflection = $reflection->getParentClass() ?: $reflection;
Reflection::setPropertyValue($throwable, 'message', sprintf('Call to undefined method %s::%s()', $reflection->getName(), $this->name));
}

throw $throwable;
}
}






public function when(callable $condition): self
{
$this->condition = Closure::fromCallable($condition);

return $this;
}




private function hasHigherOrderCallable(): bool
{
return in_array($this->name, get_class_methods(HigherOrderCallables::class), true);
}

private function getUndefinedMethodMessage(object $target, string $methodName): string
{
if (\PHP_MAJOR_VERSION >= 8) {
return sprintf(self::UNDEFINED_METHOD, sprintf('%s::%s()', $target::class, $methodName));
}

return sprintf(self::UNDEFINED_METHOD, $methodName);
}
}
