<?php

declare(strict_types=1);

namespace Pest\Logging;

use NunoMaduro\Collision\Adapters\Phpunit\State;
use Pest\Exceptions\ShouldNotHappen;
use Pest\Support\StateGenerator;
use Pest\Support\Str;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\TestSuite\TestSuite;
use PHPUnit\Framework\Exception as FrameworkException;
use PHPUnit\TestRunner\TestResult\TestResult as PhpUnitTestResult;




final class Converter
{
private const PREFIX = 'P\\';

private readonly StateGenerator $stateGenerator;




public function __construct(
private readonly string $rootPath,
) {
$this->stateGenerator = new StateGenerator;
}




public function getTestCaseMethodName(Test $test): string
{
if (! $test instanceof TestMethod) {
throw ShouldNotHappen::fromMessage('Not an instance of TestMethod');
}

return $test->testDox()->prettifiedMethodName();
}




public function getTestCaseLocation(Test $test): string
{
if (! $test instanceof TestMethod) {
throw ShouldNotHappen::fromMessage('Not an instance of TestMethod');
}

$path = $test->testDox()->prettifiedClassName();
$relativePath = $this->toRelativePath($path);


$description = $test->testDox()->prettifiedMethodName();

return "$relativePath::$description";
}




public function getExceptionMessage(Throwable $throwable): string
{
if (is_a($throwable->className(), FrameworkException::class, true)) {
return $throwable->message();
}

$buffer = $throwable->className();
$throwableMessage = $throwable->message();

if ($throwableMessage !== '') {
$buffer .= ": $throwableMessage";
}

return $buffer;
}




public function getExceptionDetails(Throwable $throwable): string
{
$buffer = $this->getStackTrace($throwable);

while ($throwable->hasPrevious()) {
$throwable = $throwable->previous();

$buffer .= sprintf(
"\nCaused by\n%s\n%s",
$throwable->description(),
$this->getStackTrace($throwable)
);
}

return $buffer;
}




public function getStackTrace(Throwable $throwable): string
{
$stackTrace = $throwable->stackTrace();


$frames = explode("\n", $stackTrace);


$frames = array_filter($frames);


$frames = array_map(
fn (string $frame): string => $this->toRelativePath($frame),
$frames
);


$frames = array_map(
fn (string $frame) => "at $frame",
$frames
);

return implode("\n", $frames);
}




public function getTestSuiteName(TestSuite $testSuite): string
{
$name = $testSuite->name();

if (! str_starts_with($name, self::PREFIX)) {
return $name;
}

return Str::after($name, self::PREFIX);
}




public function getTrimmedTestClassName(TestMethod $test): string
{
return Str::after($test->className(), self::PREFIX);
}




public function getTestSuiteLocation(TestSuite $testSuite): ?string
{
$tests = $testSuite->tests()->asArray();


if ($tests === []) {
return null;
}

$firstTest = $tests[0];
if (! $firstTest instanceof TestMethod) {
throw ShouldNotHappen::fromMessage('Not an instance of TestMethod');
}

$path = $firstTest->testDox()->prettifiedClassName();

return $this->toRelativePath($path);
}




public function getTestSuiteSize(TestSuite $testSuite): int
{
return $testSuite->count();
}




private function toRelativePath(string $path): string
{

return str_replace("$this->rootPath".DIRECTORY_SEPARATOR, '', $path);
}




public function getStateFromResult(PhpUnitTestResult $result): State
{
$events = [
...$result->testErroredEvents(),
...$result->testFailedEvents(),
...$result->testSkippedEvents(),
...array_merge(...array_values($result->testConsideredRiskyEvents())),
...$result->testMarkedIncompleteEvents(),
];

$numberOfNotPassedTests = count(
array_unique(
array_map(
function (BeforeFirstTestMethodErrored|Errored|Failed|Skipped|ConsideredRisky|MarkedIncomplete $event): string {
if ($event instanceof BeforeFirstTestMethodErrored) {
return $event->testClassName();
}

return $this->getTestCaseLocation($event->test());
},
$events
)
)
);

$numberOfPassedTests = $result->numberOfTestsRun() - $numberOfNotPassedTests;

return $this->stateGenerator->fromPhpUnitTestResult($numberOfPassedTests, $result);
}
}
