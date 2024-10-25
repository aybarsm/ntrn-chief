<?php declare(strict_types=1);








namespace PHPUnit\Logging\TeamCity;

use function assert;
use function getmypid;
use function ini_get;
use function is_a;
use function round;
use function sprintf;
use function str_replace;
use function stripos;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Event;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\InvalidArgumentException;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\TestSuiteForTestClass;
use PHPUnit\Event\TestSuite\TestSuiteForTestMethodWithDataProvider;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\Exception as FrameworkException;
use PHPUnit\TextUI\Output\Printer;

/**
@no-named-arguments


*/
final class TeamCityLogger
{
private readonly Printer $printer;
private bool $isSummaryTestCountPrinted = false;
private ?HRTime $time = null;
private ?int $flowId;





public function __construct(Printer $printer, Facade $facade)
{
$this->printer = $printer;

$this->registerSubscribers($facade);
$this->setFlowId();
}

public function testSuiteStarted(TestSuiteStarted $event): void
{
$testSuite = $event->testSuite();

if (!$this->isSummaryTestCountPrinted) {
$this->isSummaryTestCountPrinted = true;

$this->writeMessage(
'testCount',
['count' => $testSuite->count()],
);
}

$parameters = ['name' => $testSuite->name()];

if ($testSuite->isForTestClass()) {
assert($testSuite instanceof TestSuiteForTestClass);

$parameters['locationHint'] = sprintf(
'php_qn://%s::\\%s',
$testSuite->file(),
$testSuite->name(),
);
} elseif ($testSuite->isForTestMethodWithDataProvider()) {
assert($testSuite instanceof TestSuiteForTestMethodWithDataProvider);

$parameters['locationHint'] = sprintf(
'php_qn://%s::\\%s',
$testSuite->file(),
$testSuite->name(),
);

$parameters['name'] = $testSuite->methodName();
}

$this->writeMessage('testSuiteStarted', $parameters);
}

public function testSuiteFinished(TestSuiteFinished $event): void
{
$testSuite = $event->testSuite();

$parameters = ['name' => $testSuite->name()];

if ($testSuite->isForTestMethodWithDataProvider()) {
assert($testSuite instanceof TestSuiteForTestMethodWithDataProvider);

$parameters['name'] = $testSuite->methodName();
}

$this->writeMessage('testSuiteFinished', $parameters);
}

public function testPrepared(Prepared $event): void
{
$test = $event->test();

$parameters = [
'name' => $test->name(),
];

if ($test->isTestMethod()) {
assert($test instanceof TestMethod);

$parameters['locationHint'] = sprintf(
'php_qn://%s::\\%s::%s',
$test->file(),
$test->className(),
$test->name(),
);
}

$this->writeMessage('testStarted', $parameters);

$this->time = $event->telemetryInfo()->time();
}




public function testMarkedIncomplete(MarkedIncomplete $event): void
{
if ($this->time === null) {
$this->time = $event->telemetryInfo()->time();
}

$this->writeMessage(
'testIgnored',
[
'name' => $event->test()->name(),
'message' => $event->throwable()->message(),
'details' => $this->details($event->throwable()),
'duration' => $this->duration($event),
],
);
}




public function testSkipped(Skipped $event): void
{
if ($this->time === null) {
$this->time = $event->telemetryInfo()->time();
}

$parameters = [
'name' => $event->test()->name(),
'message' => $event->message(),
];

$parameters['duration'] = $this->duration($event);

$this->writeMessage('testIgnored', $parameters);
}




public function testErrored(Errored $event): void
{
if ($this->time === null) {
$this->time = $event->telemetryInfo()->time();
}

$this->writeMessage(
'testFailed',
[
'name' => $event->test()->name(),
'message' => $this->message($event->throwable()),
'details' => $this->details($event->throwable()),
'duration' => $this->duration($event),
],
);
}




public function testFailed(Failed $event): void
{
if ($this->time === null) {
$this->time = $event->telemetryInfo()->time();
}

$parameters = [
'name' => $event->test()->name(),
'message' => $this->message($event->throwable()),
'details' => $this->details($event->throwable()),
'duration' => $this->duration($event),
];

if ($event->hasComparisonFailure()) {
$parameters['type'] = 'comparisonFailure';
$parameters['actual'] = $event->comparisonFailure()->actual();
$parameters['expected'] = $event->comparisonFailure()->expected();
}

$this->writeMessage('testFailed', $parameters);
}




public function testConsideredRisky(ConsideredRisky $event): void
{
if ($this->time === null) {
$this->time = $event->telemetryInfo()->time();
}

$this->writeMessage(
'testFailed',
[
'name' => $event->test()->name(),
'message' => $event->message(),
'details' => '',
'duration' => $this->duration($event),
],
);
}




public function testFinished(Finished $event): void
{
$this->writeMessage(
'testFinished',
[
'name' => $event->test()->name(),
'duration' => $this->duration($event),
],
);

$this->time = null;
}

public function flush(): void
{
$this->printer->flush();
}





private function registerSubscribers(Facade $facade): void
{
$facade->registerSubscribers(
new TestSuiteStartedSubscriber($this),
new TestSuiteFinishedSubscriber($this),
new TestPreparedSubscriber($this),
new TestFinishedSubscriber($this),
new TestErroredSubscriber($this),
new TestFailedSubscriber($this),
new TestMarkedIncompleteSubscriber($this),
new TestSkippedSubscriber($this),
new TestConsideredRiskySubscriber($this),
new TestRunnerExecutionFinishedSubscriber($this),
);
}

private function setFlowId(): void
{
if (stripos(ini_get('disable_functions'), 'getmypid') === false) {
$this->flowId = getmypid();
}
}

private function writeMessage(string $eventName, array $parameters = []): void
{
$this->printer->print(
sprintf(
"\n##teamcity[%s",
$eventName,
),
);

if ($this->flowId !== null) {
$parameters['flowId'] = $this->flowId;
}

foreach ($parameters as $key => $value) {
$this->printer->print(
sprintf(
" %s='%s'",
$key,
$this->escape((string) $value),
),
);
}

$this->printer->print("]\n");
}




private function duration(Event $event): int
{
if ($this->time === null) {
return 0;
}

return (int) round($event->telemetryInfo()->time()->duration($this->time)->asFloat() * 1000);
}

private function escape(string $string): string
{
return str_replace(
['|', "'", "\n", "\r", ']', '['],
['||', "|'", '|n', '|r', '|]', '|['],
$string,
);
}

private function message(Throwable $throwable): string
{
if (is_a($throwable->className(), FrameworkException::class, true)) {
return $throwable->message();
}

$buffer = $throwable->className();

if (!empty($throwable->message())) {
$buffer .= ': ' . $throwable->message();
}

return $buffer;
}

private function details(Throwable $throwable): string
{
$buffer = $throwable->stackTrace();

while ($throwable->hasPrevious()) {
$throwable = $throwable->previous();

$buffer .= sprintf(
"\nCaused by\n%s\n%s",
$throwable->description(),
$throwable->stackTrace(),
);
}

return $buffer;
}
}
