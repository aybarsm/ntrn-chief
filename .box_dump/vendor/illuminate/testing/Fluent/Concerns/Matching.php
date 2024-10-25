<?php

namespace Illuminate\Testing\Fluent\Concerns;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;

use function Illuminate\Support\enum_value;

trait Matching
{







public function where(string $key, $expected): self
{
$this->has($key);

$actual = $this->prop($key);

if ($expected instanceof Closure) {
PHPUnit::assertTrue(
$expected(is_array($actual) ? Collection::make($actual) : $actual),
sprintf('Property [%s] was marked as invalid using a closure.', $this->dotPath($key))
);

return $this;
}

$expected = $expected instanceof Arrayable
? $expected->toArray()
: enum_value($expected);

$this->ensureSorted($expected);
$this->ensureSorted($actual);

PHPUnit::assertSame(
$expected,
$actual,
sprintf('Property [%s] does not match the expected value.', $this->dotPath($key))
);

return $this;
}








public function whereNot(string $key, $expected): self
{
$this->has($key);

$actual = $this->prop($key);

if ($expected instanceof Closure) {
PHPUnit::assertFalse(
$expected(is_array($actual) ? Collection::make($actual) : $actual),
sprintf('Property [%s] was marked as invalid using a closure.', $this->dotPath($key))
);

return $this;
}

$expected = $expected instanceof Arrayable
? $expected->toArray()
: enum_value($expected);

$this->ensureSorted($expected);
$this->ensureSorted($actual);

PHPUnit::assertNotSame(
$expected,
$actual,
sprintf(
'Property [%s] contains a value that should be missing: [%s, %s]',
$this->dotPath($key),
$key,
$expected
)
);

return $this;
}







public function whereAll(array $bindings): self
{
foreach ($bindings as $key => $value) {
$this->where($key, $value);
}

return $this;
}








public function whereType(string $key, $expected): self
{
$this->has($key);

$actual = $this->prop($key);

if (! is_array($expected)) {
$expected = explode('|', $expected);
}

PHPUnit::assertContains(
strtolower(gettype($actual)),
$expected,
sprintf('Property [%s] is not of expected type [%s].', $this->dotPath($key), implode('|', $expected))
);

return $this;
}







public function whereAllType(array $bindings): self
{
foreach ($bindings as $key => $value) {
$this->whereType($key, $value);
}

return $this;
}








public function whereContains(string $key, $expected)
{
$actual = Collection::make(
$this->prop($key) ?? $this->prop()
);

$missing = Collection::make($expected)
->map(fn ($search) => enum_value($search))
->reject(function ($search) use ($key, $actual) {
if ($actual->containsStrict($key, $search)) {
return true;
}

return $actual->containsStrict($search);
});

if ($missing->whereInstanceOf('Closure')->isNotEmpty()) {
PHPUnit::assertEmpty(
$missing->toArray(),
sprintf(
'Property [%s] does not contain a value that passes the truth test within the given closure.',
$key,
)
);
} else {
PHPUnit::assertEmpty(
$missing->toArray(),
sprintf(
'Property [%s] does not contain [%s].',
$key,
implode(', ', array_values($missing->toArray()))
)
);
}

return $this;
}







protected function ensureSorted(&$value): void
{
if (! is_array($value)) {
return;
}

foreach ($value as &$arg) {
$this->ensureSorted($arg);
}

ksort($value);
}







abstract protected function dotPath(string $key = ''): string;









abstract public function has(string $key, $value = null, ?Closure $scope = null);







abstract protected function prop(?string $key = null);
}
