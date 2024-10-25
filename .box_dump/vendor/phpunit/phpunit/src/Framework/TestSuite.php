<?php declare(strict_types=1);








namespace PHPUnit\Framework;

use const PHP_EOL;
use function array_keys;
use function array_map;
use function array_pop;
use function array_reverse;
use function assert;
use function call_user_func;
use function class_exists;
use function count;
use function implode;
use function is_callable;
use function is_file;
use function is_subclass_of;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use function trim;
use Iterator;
use IteratorAggregate;
use PHPUnit\Event;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Metadata\Api\Dependencies;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\Metadata\Api\HookMethods;
use PHPUnit\Metadata\Api\Requirements;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Runner\Exception as RunnerException;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\Util\Filter;
use PHPUnit\Util\Reflection;
use PHPUnit\Util\Test as TestUtil;
use ReflectionClass;
use ReflectionMethod;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use Throwable;

/**
@template-implements
@no-named-arguments



*/
class TestSuite implements IteratorAggregate, Reorderable, SelfDescribing, Test
{
/**
@psalm-var
*/
private string $name;

/**
@psalm-var
*/
private array $groups = [];

/**
@psalm-var
*/
private ?array $requiredTests = null;

/**
@psalm-var
*/
private array $tests = [];

/**
@psalm-var
*/
private ?array $providedTests = null;
private ?Factory $iteratorFilter = null;
private bool $wasRun = false;

/**
@psalm-param
*/
public static function empty(string $name): static
{
return new static($name);
}

/**
@psalm-param
*/
public static function fromClassName(string $className): static
{
assert(class_exists($className));

$class = new ReflectionClass($className);

return static::fromClassReflector($class);
}

public static function fromClassReflector(ReflectionClass $class): static
{
$testSuite = new static($class->getName());

$constructor = $class->getConstructor();

if ($constructor !== null && !$constructor->isPublic()) {
Event\Facade::emitter()->testRunnerTriggeredWarning(
sprintf(
'Class "%s" has no public constructor.',
$class->getName(),
),
);

return $testSuite;
}

foreach (Reflection::publicMethodsInTestClass($class) as $method) {
if ($method->getDeclaringClass()->getName() === Assert::class) {
continue;
}

if ($method->getDeclaringClass()->getName() === TestCase::class) {
continue;
}

if (!TestUtil::isTestMethod($method)) {
continue;
}

$testSuite->addTestMethod($class, $method);
}

if ($testSuite->isEmpty()) {
Event\Facade::emitter()->testRunnerTriggeredWarning(
sprintf(
'No tests found in class "%s".',
$class->getName(),
),
);
}

return $testSuite;
}

/**
@psalm-param
*/
final private function __construct(string $name)
{
$this->name = $name;
}




public function toString(): string
{
return $this->name();
}




public function addTest(Test $test, array $groups = []): void
{
$class = new ReflectionClass($test);

if (!$class->isAbstract()) {
$this->tests[] = $test;
$this->clearCaches();

if ($test instanceof self && empty($groups)) {
$groups = $test->groups();
}

if ($this->containsOnlyVirtualGroups($groups)) {
$groups[] = 'default';
}

foreach ($groups as $group) {
if (!isset($this->groups[$group])) {
$this->groups[$group] = [$test];
} else {
$this->groups[$group][] = $test;
}
}

if ($test instanceof TestCase) {
$test->setGroups($groups);
}
}
}






public function addTestSuite(ReflectionClass $testClass): void
{
if ($testClass->isAbstract()) {
throw new Exception(
sprintf(
'Class %s is abstract',
$testClass->getName(),
),
);
}

if (!$testClass->isSubclassOf(TestCase::class)) {
throw new Exception(
sprintf(
'Class %s is not a subclass of %s',
$testClass->getName(),
TestCase::class,
),
);
}

$this->addTest(self::fromClassReflector($testClass));
}











public function addTestFile(string $filename): void
{
if (str_ends_with($filename, '.phpt') && is_file($filename)) {
try {
$this->addTest(new PhptTestCase($filename));
} catch (RunnerException $e) {
Event\Facade::emitter()->testRunnerTriggeredWarning(
$e->getMessage(),
);
}

return;
}

try {
$this->addTestSuite(
(new TestSuiteLoader)->load($filename),
);
} catch (RunnerException $e) {
Event\Facade::emitter()->testRunnerTriggeredWarning(
$e->getMessage(),
);
}
}






public function addTestFiles(iterable $fileNames): void
{
foreach ($fileNames as $filename) {
$this->addTestFile((string) $filename);
}
}




public function count(): int
{
$numTests = 0;

foreach ($this as $test) {
$numTests += count($test);
}

return $numTests;
}

public function isEmpty(): bool
{
foreach ($this as $test) {
if (count($test) !== 0) {
return false;
}
}

return true;
}

/**
@psalm-return
*/
public function name(): string
{
return $this->name;
}

/**
@psalm-return


*/
public function groups(): array
{
return array_map(
'strval',
array_keys($this->groups),
);
}

public function groupDetails(): array
{
return $this->groups;
}









public function run(): void
{
if ($this->wasRun) {

throw new Exception('The tests aggregated by this TestSuite were already run');

}

$this->wasRun = true;

if ($this->isEmpty()) {
return;
}

$emitter = Event\Facade::emitter();
$testSuiteValueObjectForEvents = Event\TestSuite\TestSuiteBuilder::from($this);

$emitter->testSuiteStarted($testSuiteValueObjectForEvents);

if (!$this->invokeMethodsBeforeFirstTest($emitter, $testSuiteValueObjectForEvents)) {
return;
}

/**
@psalm-var */
$tests = [];

foreach ($this as $test) {
$tests[] = $test;
}

$tests = array_reverse($tests);

$this->tests = [];
$this->groups = [];

while (($test = array_pop($tests)) !== null) {
if (TestResultFacade::shouldStop()) {
$emitter->testRunnerExecutionAborted();

break;
}

$test->run();
}

$this->invokeMethodsAfterLastTest($emitter);

$emitter->testSuiteFinished($testSuiteValueObjectForEvents);
}

/**
@psalm-return


*/
public function tests(): array
{
return $this->tests;
}

/**
@psalm-param


*/
public function setTests(array $tests): void
{
$this->tests = $tests;
}






public function markTestSuiteSkipped(string $message = ''): never
{
throw new SkippedTestSuiteError($message);
}




public function getIterator(): Iterator
{
$iterator = new TestSuiteIterator($this);

if ($this->iteratorFilter !== null) {
$iterator = $this->iteratorFilter->factory($iterator, $this);
}

return $iterator;
}

public function injectFilter(Factory $filter): void
{
$this->iteratorFilter = $filter;

foreach ($this as $test) {
if ($test instanceof self) {
$test->injectFilter($filter);
}
}
}

/**
@psalm-return
*/
public function provides(): array
{
if ($this->providedTests === null) {
$this->providedTests = [];

if (is_callable($this->sortId(), true)) {
$this->providedTests[] = new ExecutionOrderDependency($this->sortId());
}

foreach ($this->tests as $test) {
if (!($test instanceof Reorderable)) {
continue;
}

$this->providedTests = ExecutionOrderDependency::mergeUnique($this->providedTests, $test->provides());
}
}

return $this->providedTests;
}

/**
@psalm-return
*/
public function requires(): array
{
if ($this->requiredTests === null) {
$this->requiredTests = [];

foreach ($this->tests as $test) {
if (!($test instanceof Reorderable)) {
continue;
}

$this->requiredTests = ExecutionOrderDependency::mergeUnique(
ExecutionOrderDependency::filterInvalid($this->requiredTests),
$test->requires(),
);
}

$this->requiredTests = ExecutionOrderDependency::diff($this->requiredTests, $this->provides());
}

return $this->requiredTests;
}

public function sortId(): string
{
return $this->name() . '::class';
}

/**
@psalm-assert-if-true
*/
public function isForTestClass(): bool
{
return class_exists($this->name, false) && is_subclass_of($this->name, TestCase::class);
}





protected function addTestMethod(ReflectionClass $class, ReflectionMethod $method): void
{
$className = $class->getName();
$methodName = $method->getName();

assert(!empty($methodName));

try {
$test = (new TestBuilder)->build($class, $methodName);
} catch (InvalidDataProviderException $e) {
Event\Facade::emitter()->testTriggeredPhpunitError(
new TestMethod(
$className,
$methodName,
$class->getFileName(),
$method->getStartLine(),
Event\Code\TestDoxBuilder::fromClassNameAndMethodName(
$className,
$methodName,
),
MetadataCollection::fromArray([]),
Event\TestData\TestDataCollection::fromArray([]),
),
sprintf(
"The data provider specified for %s::%s is invalid\n%s",
$className,
$methodName,
$this->throwableToString($e),
),
);

return;
}

if ($test instanceof TestCase || $test instanceof DataProviderTestSuite) {
$test->setDependencies(
Dependencies::dependencies($class->getName(), $methodName),
);
}

$this->addTest(
$test,
(new Groups)->groups($class->getName(), $methodName),
);
}

private function clearCaches(): void
{
$this->providedTests = null;
$this->requiredTests = null;
}

private function containsOnlyVirtualGroups(array $groups): bool
{
foreach ($groups as $group) {
if (!str_starts_with($group, '__phpunit_')) {
return false;
}
}

return true;
}

private function methodDoesNotExistOrIsDeclaredInTestCase(string $methodName): bool
{
$reflector = new ReflectionClass($this->name);

return !$reflector->hasMethod($methodName) ||
$reflector->getMethod($methodName)->getDeclaringClass()->getName() === TestCase::class;
}




private function throwableToString(Throwable $t): string
{
$message = $t->getMessage();

if (empty(trim($message))) {
$message = '<no message>';
}

if ($t instanceof InvalidDataProviderException) {
return sprintf(
"%s\n%s",
$message,
Filter::getFilteredStacktrace($t),
);
}

return sprintf(
"%s: %s\n%s",
$t::class,
$message,
Filter::getFilteredStacktrace($t),
);
}





private function invokeMethodsBeforeFirstTest(Event\Emitter $emitter, Event\TestSuite\TestSuite $testSuiteValueObjectForEvents): bool
{
if (!$this->isForTestClass()) {
return true;
}

$methodsCalledBeforeFirstTest = [];

$beforeClassMethods = (new HookMethods)->hookMethods($this->name)['beforeClass'];

try {
foreach ($beforeClassMethods as $beforeClassMethod) {
if ($this->methodDoesNotExistOrIsDeclaredInTestCase($beforeClassMethod)) {
continue;
}

if ($missingRequirements = (new Requirements)->requirementsNotSatisfiedFor($this->name, $beforeClassMethod)) {
$this->markTestSuiteSkipped(implode(PHP_EOL, $missingRequirements));
}

$methodCalledBeforeFirstTest = new Event\Code\ClassMethod(
$this->name,
$beforeClassMethod,
);

$emitter->testBeforeFirstTestMethodCalled(
$this->name,
$methodCalledBeforeFirstTest,
);

$methodsCalledBeforeFirstTest[] = $methodCalledBeforeFirstTest;

call_user_func([$this->name, $beforeClassMethod]);
}
} catch (SkippedTest|SkippedTestSuiteError $e) {
$emitter->testSuiteSkipped(
$testSuiteValueObjectForEvents,
$e->getMessage(),
);

return false;
} catch (Throwable $t) {
assert(isset($methodCalledBeforeFirstTest));

$emitter->testBeforeFirstTestMethodErrored(
$this->name,
$methodCalledBeforeFirstTest,
Event\Code\ThrowableBuilder::from($t),
);

if (!empty($methodsCalledBeforeFirstTest)) {
$emitter->testBeforeFirstTestMethodFinished(
$this->name,
...$methodsCalledBeforeFirstTest,
);
}

return false;
}

if (!empty($methodsCalledBeforeFirstTest)) {
$emitter->testBeforeFirstTestMethodFinished(
$this->name,
...$methodsCalledBeforeFirstTest,
);
}

return true;
}

private function invokeMethodsAfterLastTest(Event\Emitter $emitter): void
{
if (!$this->isForTestClass()) {
return;
}

$methodsCalledAfterLastTest = [];

$afterClassMethods = (new HookMethods)->hookMethods($this->name)['afterClass'];

foreach ($afterClassMethods as $afterClassMethod) {
if ($this->methodDoesNotExistOrIsDeclaredInTestCase($afterClassMethod)) {
continue;
}

try {
call_user_func([$this->name, $afterClassMethod]);

$methodCalledAfterLastTest = new Event\Code\ClassMethod(
$this->name,
$afterClassMethod,
);

$emitter->testAfterLastTestMethodCalled(
$this->name,
$methodCalledAfterLastTest,
);

$methodsCalledAfterLastTest[] = $methodCalledAfterLastTest;
} catch (Throwable) {

}
}

if (!empty($methodsCalledAfterLastTest)) {
$emitter->testAfterLastTestMethodFinished(
$this->name,
...$methodsCalledAfterLastTest,
);
}
}
}
