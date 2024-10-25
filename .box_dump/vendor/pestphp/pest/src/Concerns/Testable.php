<?php

declare(strict_types=1);

namespace Pest\Concerns;

use Closure;
use Pest\Exceptions\DatasetArgsCountMismatch;
use Pest\Support\ChainableClosure;
use Pest\Support\ExceptionTrace;
use Pest\Support\Reflection;
use Pest\TestSuite;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionFunction;
use Throwable;

/**
@mixin


*/
trait Testable
{



private string $__description;




private static string $__latestDescription;




public ?string $__describing = null;




private Closure $__test;




private ?Closure $__beforeEach = null;




private ?Closure $__afterEach = null;




private static ?Closure $__beforeAll = null;




private static ?Closure $__afterAll = null;




private array $__snapshotChanges = [];




public static function flush(): void
{
self::$__beforeAll = null;
self::$__afterAll = null;
}




public function __construct(string $name)
{
parent::__construct($name);

$test = TestSuite::getInstance()->tests->get(self::$__filename);

if ($test->hasMethod($name)) {
$method = $test->getMethod($name);
$this->__description = self::$__latestDescription = $method->description;
$this->__describing = $method->describing;
$this->__test = $method->getClosure($this);
}
}




public function __addBeforeAll(?Closure $hook): void
{
if (! $hook instanceof \Closure) {
return;
}

self::$__beforeAll = (self::$__beforeAll instanceof Closure)
? ChainableClosure::boundStatically(self::$__beforeAll, $hook)
: $hook;
}




public function __addAfterAll(?Closure $hook): void
{
if (! $hook instanceof \Closure) {
return;
}

self::$__afterAll = (self::$__afterAll instanceof Closure)
? ChainableClosure::boundStatically(self::$__afterAll, $hook)
: $hook;
}




public function __addBeforeEach(?Closure $hook): void
{
$this->__addHook('__beforeEach', $hook);
}




public function __addAfterEach(?Closure $hook): void
{
$this->__addHook('__afterEach', $hook);
}




private function __addHook(string $property, ?Closure $hook): void
{
if (! $hook instanceof \Closure) {
return;
}

$this->{$property} = ($this->{$property} instanceof Closure)
? ChainableClosure::bound($this->{$property}, $hook)
: $hook;
}




public static function setUpBeforeClass(): void
{
parent::setUpBeforeClass();

$beforeAll = TestSuite::getInstance()->beforeAll->get(self::$__filename);

if (self::$__beforeAll instanceof Closure) {
$beforeAll = ChainableClosure::boundStatically(self::$__beforeAll, $beforeAll);
}

call_user_func(Closure::bind($beforeAll, null, self::class));
}




public static function tearDownAfterClass(): void
{
$afterAll = TestSuite::getInstance()->afterAll->get(self::$__filename);

if (self::$__afterAll instanceof Closure) {
$afterAll = ChainableClosure::boundStatically(self::$__afterAll, $afterAll);
}

call_user_func(Closure::bind($afterAll, null, self::class));

parent::tearDownAfterClass();
}




protected function setUp(): void
{
TestSuite::getInstance()->test = $this;

$method = TestSuite::getInstance()->tests->get(self::$__filename)->getMethod($this->name());

$description = $this->dataName() ? $method->description.' with '.$this->dataName() : $method->description;
$description = htmlspecialchars(html_entity_decode($description), ENT_NOQUOTES);

if ($method->repetitions > 1) {
$matches = [];
preg_match('/\((.*?)\)/', $description, $matches);

if (count($matches) > 1) {
if (str_contains($description, 'with '.$matches[0].' /')) {
$description = str_replace('with '.$matches[0].' /', '', $description);
} else {
$description = str_replace('with '.$matches[0], '', $description);
}
}

$description .= ' @ repetition '.($matches[1].' of '.$method->repetitions);
}

$this->__description = self::$__latestDescription = $description;

parent::setUp();

$beforeEach = TestSuite::getInstance()->beforeEach->get(self::$__filename)[1];

if ($this->__beforeEach instanceof Closure) {
$beforeEach = ChainableClosure::bound($this->__beforeEach, $beforeEach);
}

$this->__callClosure($beforeEach, func_get_args());
}




protected function tearDown(): void
{
$afterEach = TestSuite::getInstance()->afterEach->get(self::$__filename);

if ($this->__afterEach instanceof Closure) {
$afterEach = ChainableClosure::bound($this->__afterEach, $afterEach);
}

try {
$this->__callClosure($afterEach, func_get_args());
} finally {
parent::tearDown();

TestSuite::getInstance()->test = null;
}
}






private function __runTest(Closure $closure, ...$args): mixed
{
$arguments = $this->__resolveTestArguments($args);
$this->__ensureDatasetArgumentNumberMatches($arguments);

return $this->__callClosure($closure, $arguments);
}






private function __resolveTestArguments(array $arguments): array
{
$method = TestSuite::getInstance()->tests->get(self::$__filename)->getMethod($this->name());

if ($method->repetitions > 1) {
array_shift($arguments);
}

$underlyingTest = Reflection::getFunctionVariable($this->__test, 'closure');
$testParameterTypes = array_values(Reflection::getFunctionArguments($underlyingTest));

if (count($arguments) !== 1) {
foreach ($arguments as $argumentIndex => $argumentValue) {
if (! $argumentValue instanceof Closure) {
continue;
}

if (in_array($testParameterTypes[$argumentIndex], [Closure::class, 'callable', 'mixed'])) {
continue;
}

$arguments[$argumentIndex] = $this->__callClosure($argumentValue, []);
}

return $arguments;
}

if (! $arguments[0] instanceof Closure) {
return $arguments;
}

if (isset($testParameterTypes[0]) && in_array($testParameterTypes[0], [Closure::class, 'callable'])) {
return $arguments;
}

$boundDatasetResult = $this->__callClosure($arguments[0], []);
if (count($testParameterTypes) === 1) {
return [$boundDatasetResult];
}
if (! is_array($boundDatasetResult)) {
return [$boundDatasetResult];
}

return array_values($boundDatasetResult);
}







private function __ensureDatasetArgumentNumberMatches(array $arguments): void
{
if ($arguments === []) {
return;
}

$underlyingTest = Reflection::getFunctionVariable($this->__test, 'closure');
$testReflection = new ReflectionFunction($underlyingTest);
$requiredParametersCount = $testReflection->getNumberOfRequiredParameters();
$suppliedParametersCount = count($arguments);

if ($suppliedParametersCount >= $requiredParametersCount) {
return;
}

throw new DatasetArgsCountMismatch($requiredParametersCount, $suppliedParametersCount);
}




private function __callClosure(Closure $closure, array $arguments): mixed
{
return ExceptionTrace::ensure(fn (): mixed => call_user_func_array(Closure::bind($closure, $this, $this::class), $arguments));
}

/**
@postCondition */
protected function __MarkTestIncompleteIfSnapshotHaveChanged(): void
{
if (count($this->__snapshotChanges) === 0) {
return;
}

if (count($this->__snapshotChanges) === 1) {
$this->markTestIncomplete($this->__snapshotChanges[0]);

return;
}

$messages = implode(PHP_EOL, array_map(static fn (string $message): string => '- $message', $this->__snapshotChanges));

$this->markTestIncomplete($messages);
}




public static function getPrintableTestCaseName(): string
{
return preg_replace('/P\\\/', '', self::class, 1);
}




public function getPrintableTestCaseMethodName(): string
{
return $this->__description;
}




public static function getLatestPrintableTestCaseMethodName(): string
{
return self::$__latestDescription;
}
}
