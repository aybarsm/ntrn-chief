<?php

declare(strict_types=1);

namespace Pest\PendingCalls;

use Closure;
use Pest\Exceptions\InvalidArgumentException;
use Pest\Factories\Covers\CoversClass;
use Pest\Factories\Covers\CoversFunction;
use Pest\Factories\Covers\CoversNothing;
use Pest\Factories\TestCaseMethodFactory;
use Pest\PendingCalls\Concerns\Describable;
use Pest\Plugins\Only;
use Pest\Support\Backtrace;
use Pest\Support\Exporter;
use Pest\Support\HigherOrderCallables;
use Pest\Support\NullClosure;
use Pest\Support\Str;
use Pest\TestSuite;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

/**
@mixin


*/
final class TestCall
{
use Describable;




public readonly TestCaseMethodFactory $testCaseMethod;




private readonly bool $descriptionLess;




public function __construct(
private readonly TestSuite $testSuite,
private readonly string $filename,
?string $description = null,
?Closure $closure = null
) {
$this->testCaseMethod = new TestCaseMethodFactory($filename, $description, $closure);

$this->descriptionLess = $description === null;

$this->describing = DescribeCall::describing();

$this->testSuite->beforeEach->get($this->filename)[0]($this);
}




public function fails(?string $message = null): self
{
return $this->throws(AssertionFailedError::class, $message);
}




public function throws(string|int $exception, ?string $exceptionMessage = null, ?int $exceptionCode = null): self
{
if (is_int($exception)) {
$exceptionCode = $exception;
} elseif (class_exists($exception)) {
$this->testCaseMethod
->proxies
->add(Backtrace::file(), Backtrace::line(), 'expectException', [$exception]);
} else {
$exceptionMessage = $exception;
}

if (is_string($exceptionMessage)) {
$this->testCaseMethod
->proxies
->add(Backtrace::file(), Backtrace::line(), 'expectExceptionMessage', [$exceptionMessage]);
}

if (is_int($exceptionCode)) {
$this->testCaseMethod
->proxies
->add(Backtrace::file(), Backtrace::line(), 'expectExceptionCode', [$exceptionCode]);
}

return $this;
}






public function throwsIf(callable|bool $condition, string|int $exception, ?string $exceptionMessage = null, ?int $exceptionCode = null): self
{
$condition = is_callable($condition)
? $condition
: static fn (): bool => $condition;

if ($condition()) {
return $this->throws($exception, $exceptionMessage, $exceptionCode);
}

return $this;
}






public function throwsUnless(callable|bool $condition, string|int $exception, ?string $exceptionMessage = null, ?int $exceptionCode = null): self
{
$condition = is_callable($condition)
? $condition
: static fn (): bool => $condition;

if (! $condition()) {
return $this->throws($exception, $exceptionMessage, $exceptionCode);
}

return $this;
}







public function with(Closure|iterable|string ...$data): self
{
foreach ($data as $dataset) {
$this->testCaseMethod->datasets[] = $dataset;
}

return $this;
}




public function depends(string ...$depends): self
{
foreach ($depends as $depend) {
$this->testCaseMethod->depends[] = $depend;
}

return $this;
}




public function group(string ...$groups): self
{
foreach ($groups as $group) {
$this->testCaseMethod->groups[] = $group;
}

return $this;
}




public function only(): self
{
Only::enable($this);

return $this;
}




public function skip(Closure|bool|string $conditionOrMessage = true, string $message = ''): self
{
$condition = is_string($conditionOrMessage)
? NullClosure::create()
: $conditionOrMessage;

$condition = is_callable($condition)
? $condition
: fn (): bool => $condition;

$message = is_string($conditionOrMessage)
? $conditionOrMessage
: $message;


$condition = $condition->bindTo(null);

$this->testCaseMethod
->chains
->addWhen($condition, $this->filename, Backtrace::line(), 'markTestSkipped', [$message]);

return $this;
}




public function skipOnPhp(string $version): self
{
if (mb_strlen($version) < 2) {
throw new InvalidArgumentException('The version must start with [<] or [>].');
}

if (str_starts_with($version, '>=') || str_starts_with($version, '<=')) {
$operator = substr($version, 0, 2);
$version = substr($version, 2);
} elseif (str_starts_with($version, '>') || str_starts_with($version, '<')) {
$operator = $version[0];
$version = substr($version, 1);

} elseif (is_numeric($version[0])) {
$operator = '==';
} else {
throw new InvalidArgumentException('The version must start with [<, >, <=, >=] or a number.');
}

return $this->skip(version_compare(PHP_VERSION, $version, $operator), sprintf('This test is skipped on PHP [%s%s].', $operator, $version));
}




public function skipOnWindows(): self
{
return $this->skipOnOs('Windows', 'This test is skipped on [Windows].');
}




public function skipOnMac(): self
{
return $this->skipOnOs('Darwin', 'This test is skipped on [Mac].');
}




public function skipOnLinux(): self
{
return $this->skipOnOs('Linux', 'This test is skipped on [Linux].');
}




private function skipOnOs(string $osFamily, string $message): self
{
return $osFamily === PHP_OS_FAMILY
? $this->skip($message)
: $this;
}




public function onlyOnWindows(): self
{
return $this->skipOnMac()->skipOnLinux();
}




public function onlyOnMac(): self
{
return $this->skipOnWindows()->skipOnLinux();
}




public function onlyOnLinux(): self
{
return $this->skipOnWindows()->skipOnMac();
}




public function repeat(int $times): self
{
if ($times < 1) {
throw new InvalidArgumentException('The number of repetitions must be greater than 0.');
}

$this->testCaseMethod->repetitions = $times;

return $this;
}




public function todo(): self
{
$this->skip('__TODO__');

$this->testCaseMethod->todo = true;

return $this;
}




public function covers(string ...$classesOrFunctions): self
{
foreach ($classesOrFunctions as $classOrFunction) {
$isClass = class_exists($classOrFunction) || trait_exists($classOrFunction);
$isMethod = function_exists($classOrFunction);

if (! $isClass && ! $isMethod) {
throw new InvalidArgumentException(sprintf('No class or method named "%s" has been found.', $classOrFunction));
}

if ($isClass) {
$this->coversClass($classOrFunction);
} else {
$this->coversFunction($classOrFunction);
}
}

return $this;
}




public function coversClass(string ...$classes): self
{
foreach ($classes as $class) {
$this->testCaseMethod->covers[] = new CoversClass($class);
}

return $this;
}




public function coversFunction(string ...$functions): self
{
foreach ($functions as $function) {
$this->testCaseMethod->covers[] = new CoversFunction($function);
}

return $this;
}




public function coversNothing(): self
{
$this->testCaseMethod->covers = [new CoversNothing];

return $this;
}






public function throwsNoExceptions(): self
{
$this->testCaseMethod->proxies->add(Backtrace::file(), Backtrace::line(), 'expectNotToPerformAssertions', []);

return $this;
}




public function __get(string $name): self
{
return $this->addChain(Backtrace::file(), Backtrace::line(), $name);
}






public function __call(string $name, array $arguments): self
{
return $this->addChain(Backtrace::file(), Backtrace::line(), $name, $arguments);
}






private function addChain(string $file, int $line, string $name, ?array $arguments = null): self
{
$exporter = Exporter::default();

$this->testCaseMethod
->chains
->add($file, $line, $name, $arguments);

if ($this->descriptionLess) {
Exporter::default();

if ($this->testCaseMethod->description !== null) {
$this->testCaseMethod->description .= ' → ';
}
$this->testCaseMethod->description .= $arguments === null
? $name
: sprintf('%s %s', $name, $exporter->shortenedRecursiveExport($arguments));
}

return $this;
}




public function __destruct()
{
if (! is_null($this->describing)) {
$this->testCaseMethod->describing = $this->describing;
$this->testCaseMethod->description = Str::describe($this->describing, $this->testCaseMethod->description); 
}

$this->testSuite->tests->set($this->testCaseMethod);
}
}
