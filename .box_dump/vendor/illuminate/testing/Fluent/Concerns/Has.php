<?php

namespace Illuminate\Testing\Fluent\Concerns;

use Closure;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert as PHPUnit;

trait Has
{







public function count($key, ?int $length = null): self
{
if (is_null($length)) {
$path = $this->dotPath();

PHPUnit::assertCount(
$key,
$this->prop(),
$path
? sprintf('Property [%s] does not have the expected size.', $path)
: sprintf('Root level does not have the expected size.')
);

return $this;
}

PHPUnit::assertCount(
$length,
$this->prop($key),
sprintf('Property [%s] does not have the expected size.', $this->dotPath($key))
);

return $this;
}








public function countBetween(int|string $min, int|string $max): self
{
$path = $this->dotPath();

$prop = $this->prop();

PHPUnit::assertGreaterThanOrEqual(
$min,
count($prop),
$path
? sprintf('Property [%s] size is not greater than or equal to [%s].', $path, $min)
: sprintf('Root level size is not greater than or equal to [%s].', $min)
);

PHPUnit::assertLessThanOrEqual(
$max,
count($prop),
$path
? sprintf('Property [%s] size is not less than or equal to [%s].', $path, $max)
: sprintf('Root level size is not less than or equal to [%s].', $max)
);

return $this;
}









public function has($key, $length = null, ?Closure $callback = null): self
{
$prop = $this->prop();

if (is_int($key) && is_null($length)) {
return $this->count($key);
}

PHPUnit::assertTrue(
Arr::has($prop, $key),
sprintf('Property [%s] does not exist.', $this->dotPath($key))
);

$this->interactsWith($key);

if (! is_null($callback)) {
return $this->has($key, function (self $scope) use ($length, $callback) {
return $scope
->tap(function (self $scope) use ($length) {
if (! is_null($length)) {
$scope->count($length);
}
})
->first($callback)
->etc();
});
}

if (is_callable($length)) {
return $this->scope($key, $length);
}

if (! is_null($length)) {
return $this->count($key, $length);
}

return $this;
}







public function hasAll($key): self
{
$keys = is_array($key) ? $key : func_get_args();

foreach ($keys as $prop => $count) {
if (is_int($prop)) {
$this->has($count);
} else {
$this->has($prop, $count);
}
}

return $this;
}







public function hasAny($key): self
{
$keys = is_array($key) ? $key : func_get_args();

PHPUnit::assertTrue(
Arr::hasAny($this->prop(), $keys),
sprintf('None of properties [%s] exist.', implode(', ', $keys))
);

foreach ($keys as $key) {
$this->interactsWith($key);
}

return $this;
}







public function missingAll($key): self
{
$keys = is_array($key) ? $key : func_get_args();

foreach ($keys as $prop) {
$this->missing($prop);
}

return $this;
}







public function missing(string $key): self
{
PHPUnit::assertNotTrue(
Arr::has($this->prop(), $key),
sprintf('Property [%s] was found while it was expected to be missing.', $this->dotPath($key))
);

return $this;
}







abstract protected function dotPath(string $key = ''): string;







abstract protected function interactsWith(string $key): void;







abstract protected function prop(?string $key = null);








abstract protected function scope(string $key, Closure $callback);






abstract public function etc();







abstract public function first(Closure $callback);
}
