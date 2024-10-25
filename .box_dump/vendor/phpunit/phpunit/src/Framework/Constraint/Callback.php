<?php declare(strict_types=1);








namespace PHPUnit\Framework\Constraint;

use Closure;
use ReflectionFunction;

/**
@psalm-template
@no-named-arguments

*/
final class Callback extends Constraint
{
/**
@psalm-var
*/
private readonly mixed $callback;

/**
@psalm-param
*/
public function __construct(callable $callback)
{
$this->callback = $callback;
}




public function toString(): string
{
return 'is accepted by specified callback';
}

/**
@psalm-suppress
*/
public function isVariadic(): bool
{
foreach ((new ReflectionFunction(Closure::fromCallable($this->callback)))->getParameters() as $parameter) {
if ($parameter->isVariadic()) {
return true;
}
}

return false;
}

/**
@psalm-param
@psalm-suppress




*/
protected function matches(mixed $other): bool
{
if ($this->isVariadic()) {
return ($this->callback)(...$other);
}

return ($this->callback)($other);
}
}
