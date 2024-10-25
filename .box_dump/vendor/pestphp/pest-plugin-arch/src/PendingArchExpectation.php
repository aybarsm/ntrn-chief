<?php

declare(strict_types=1);

namespace Pest\Arch;

use Closure;
use Pest\Arch\Contracts\ArchExpectation;
use Pest\Expectation;
use Pest\Expectations\HigherOrderExpectation;
use PHPUnit\Architecture\Elements\ObjectDescription;

/**
@mixin


*/
final class PendingArchExpectation
{



private bool $opposite = false;






public function __construct(
private readonly Expectation $expectation,
private array $excludeCallbacks,
) {
}




public function classes(): self
{
$this->excludeCallbacks[] = fn (ObjectDescription $object): bool => ! class_exists($object->name) || enum_exists($object->name);

return $this;
}




public function interfaces(): self
{
$this->excludeCallbacks[] = fn (ObjectDescription $object): bool => ! interface_exists($object->name);

return $this;
}




public function traits(): self
{
$this->excludeCallbacks[] = fn (ObjectDescription $object): bool => ! trait_exists($object->name);

return $this;
}




public function enums(): self
{
$this->excludeCallbacks[] = fn (ObjectDescription $object): bool => ! enum_exists($object->name);

return $this;
}




public function not(): self
{
$this->opposite = ! $this->opposite;

return $this;
}






public function __call(string $name, array $arguments): ArchExpectation
{
$expectation = $this->opposite ? $this->expectation->not() : $this->expectation;

/**
@@var $archExpectation SingleArchExpectation */
$archExpectation = $expectation->{$name}(...$arguments); 

if ($archExpectation instanceof HigherOrderExpectation) {
$originalExpectation = (fn (): \Pest\Expectation => $this->original)->call($archExpectation);
} else {
$originalExpectation = $archExpectation;
}

$originalExpectation->mergeExcludeCallbacks($this->excludeCallbacks);

return $archExpectation;
}




public function __get(string $name): mixed
{
return $this->{$name}(); 
}
}
