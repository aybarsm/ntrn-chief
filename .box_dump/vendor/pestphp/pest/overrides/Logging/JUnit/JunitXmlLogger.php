<?php

declare(strict_types=1);









namespace PHPUnit\Logging\JUnit;

use DOMDocument;
use DOMElement;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\InvalidArgumentException;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\TextUI\Output\Printer;
use PHPUnit\Util\Xml;

use function assert;
use function basename;
use function is_int;
use function sprintf;
use function str_replace;
use function trim;




final class JunitXmlLogger
{
private readonly Printer $printer;

private readonly \Pest\Logging\Converter $converter; 

private DOMDocument $document;

private DOMElement $root;




private array $testSuites = [];

/**
@psalm-var
*/
private array $testSuiteTests = [0];

/**
@psalm-var
*/
private array $testSuiteAssertions = [0];

/**
@psalm-var
*/
private array $testSuiteErrors = [0];

/**
@psalm-var
*/
private array $testSuiteFailures = [0];

/**
@psalm-var
*/
private array $testSuiteSkipped = [0];

/**
@psalm-var
*/
private array $testSuiteTimes = [0];

private int $testSuiteLevel = 0;

private ?DOMElement $currentTestCase = null;

private ?HRTime $time = null;

private bool $prepared = false;

private bool $preparationFailed = false;





public function __construct(Printer $printer, Facade $facade)
{
$this->printer = $printer;
$this->converter = new \Pest\Logging\Converter(\Pest\Support\Container::getInstance()->get(\Pest\TestSuite::class)->rootPath); 

$this->registerSubscribers($facade);
$this->createDocument();
}

public function flush(): void
{
$this->printer->print($this->document->saveXML());

$this->printer->flush();
}

public function testSuiteStarted(Started $event): void
{
$testSuite = $this->document->createElement('testsuite');
$testSuite->setAttribute('name', $this->converter->getTestSuiteName($event->testSuite())); 

if ($event->testSuite()->isForTestClass()) {
$testSuite->setAttribute('file', $this->converter->getTestSuiteLocation($event->testSuite()) ?? ''); 
}

if ($this->testSuiteLevel > 0) {
$this->testSuites[$this->testSuiteLevel]->appendChild($testSuite);
} else {
$this->root->appendChild($testSuite);
}

$this->testSuiteLevel++;
$this->testSuites[$this->testSuiteLevel] = $testSuite;
$this->testSuiteTests[$this->testSuiteLevel] = 0;
$this->testSuiteAssertions[$this->testSuiteLevel] = 0;
$this->testSuiteErrors[$this->testSuiteLevel] = 0;
$this->testSuiteFailures[$this->testSuiteLevel] = 0;
$this->testSuiteSkipped[$this->testSuiteLevel] = 0;
$this->testSuiteTimes[$this->testSuiteLevel] = 0;
}

public function testSuiteFinished(): void
{
$this->testSuites[$this->testSuiteLevel]->setAttribute(
'tests',
(string) $this->testSuiteTests[$this->testSuiteLevel],
);

$this->testSuites[$this->testSuiteLevel]->setAttribute(
'assertions',
(string) $this->testSuiteAssertions[$this->testSuiteLevel],
);

$this->testSuites[$this->testSuiteLevel]->setAttribute(
'errors',
(string) $this->testSuiteErrors[$this->testSuiteLevel],
);

$this->testSuites[$this->testSuiteLevel]->setAttribute(
'failures',
(string) $this->testSuiteFailures[$this->testSuiteLevel],
);

$this->testSuites[$this->testSuiteLevel]->setAttribute(
'skipped',
(string) $this->testSuiteSkipped[$this->testSuiteLevel],
);

$this->testSuites[$this->testSuiteLevel]->setAttribute(
'time',
sprintf('%F', $this->testSuiteTimes[$this->testSuiteLevel]),
);

if ($this->testSuiteLevel > 1) {
$this->testSuiteTests[$this->testSuiteLevel - 1] += $this->testSuiteTests[$this->testSuiteLevel];
$this->testSuiteAssertions[$this->testSuiteLevel - 1] += $this->testSuiteAssertions[$this->testSuiteLevel];
$this->testSuiteErrors[$this->testSuiteLevel - 1] += $this->testSuiteErrors[$this->testSuiteLevel];
$this->testSuiteFailures[$this->testSuiteLevel - 1] += $this->testSuiteFailures[$this->testSuiteLevel];
$this->testSuiteSkipped[$this->testSuiteLevel - 1] += $this->testSuiteSkipped[$this->testSuiteLevel];
$this->testSuiteTimes[$this->testSuiteLevel - 1] += $this->testSuiteTimes[$this->testSuiteLevel];
}

$this->testSuiteLevel--;
}




public function testPreparationStarted(PreparationStarted $event): void
{
$this->createTestCase($event);
}




public function testPreparationFailed(): void
{
$this->preparationFailed = true;
}




public function testPrepared(): void
{
$this->prepared = true;
}




public function testFinished(Finished $event): void
{
if ($this->preparationFailed) {
return;
}

$this->handleFinish($event->telemetryInfo(), $event->numberOfAssertionsPerformed());
}




public function testMarkedIncomplete(MarkedIncomplete $event): void
{
$this->handleIncompleteOrSkipped($event);
}




public function testSkipped(Skipped $event): void
{
$this->handleIncompleteOrSkipped($event);
}




public function testErrored(Errored $event): void
{
$this->handleFault($event, 'error');

$this->testSuiteErrors[$this->testSuiteLevel]++;
}




public function testFailed(Failed $event): void
{
$this->handleFault($event, 'failure');

$this->testSuiteFailures[$this->testSuiteLevel]++;
}




private function handleFinish(Info $telemetryInfo, int $numberOfAssertionsPerformed): void
{
assert($this->currentTestCase !== null);
assert($this->time !== null);

$time = $telemetryInfo->time()->duration($this->time)->asFloat();

$this->testSuiteAssertions[$this->testSuiteLevel] += $numberOfAssertionsPerformed;

$this->currentTestCase->setAttribute(
'assertions',
(string) $numberOfAssertionsPerformed,
);

$this->currentTestCase->setAttribute(
'time',
sprintf('%F', $time),
);

$this->testSuites[$this->testSuiteLevel]->appendChild(
$this->currentTestCase,
);

$this->testSuiteTests[$this->testSuiteLevel]++;
$this->testSuiteTimes[$this->testSuiteLevel] += $time;

$this->currentTestCase = null;
$this->time = null;
$this->prepared = false;
}





private function registerSubscribers(Facade $facade): void
{
$facade->registerSubscribers(
new TestSuiteStartedSubscriber($this),
new TestSuiteFinishedSubscriber($this),
new TestPreparationStartedSubscriber($this),
new TestPreparationFailedSubscriber($this),
new TestPreparedSubscriber($this),
new TestFinishedSubscriber($this),
new TestErroredSubscriber($this),
new TestFailedSubscriber($this),
new TestMarkedIncompleteSubscriber($this),
new TestSkippedSubscriber($this),
new TestRunnerExecutionFinishedSubscriber($this),
);
}

private function createDocument(): void
{
$this->document = new DOMDocument('1.0', 'UTF-8');
$this->document->formatOutput = true;

$this->root = $this->document->createElement('testsuites');
$this->document->appendChild($this->root);
}




private function handleFault(Errored|Failed $event, string $type): void
{
if (! $this->prepared) {
$this->createTestCase($event);
}

assert($this->currentTestCase !== null);

$buffer = $this->converter->getTestCaseMethodName($event->test()); 

$throwable = $event->throwable();
$buffer .= trim(
$this->converter->getExceptionMessage($throwable).PHP_EOL. 
$this->converter->getExceptionDetails($throwable), 
);

$fault = $this->document->createElement(
$type,
Xml::prepareString($buffer),
);

$fault->setAttribute('type', $throwable->className());

$this->currentTestCase->appendChild($fault);

if (! $this->prepared) {
$this->handleFinish($event->telemetryInfo(), 0);
}
}




private function handleIncompleteOrSkipped(MarkedIncomplete|Skipped $event): void
{
if (! $this->prepared) {
$this->createTestCase($event);
}

assert($this->currentTestCase !== null);

$skipped = $this->document->createElement('skipped');

$this->currentTestCase->appendChild($skipped);

$this->testSuiteSkipped[$this->testSuiteLevel]++;

if (! $this->prepared) {
$this->handleFinish($event->telemetryInfo(), 0);
}
}




private function testAsString(Test $test): string
{
if ($test->isPhpt()) {
return basename($test->file());
}

assert($test instanceof TestMethod);

return sprintf(
'%s::%s%s',
$test->className(),
$this->name($test),
PHP_EOL,
);
}




private function name(Test $test): string
{
if ($test->isPhpt()) {
return basename($test->file());
}

assert($test instanceof TestMethod);

if (! $test->testData()->hasDataFromDataProvider()) {
return $test->methodName();
}

$dataSetName = $test->testData()->dataFromDataProvider()->dataSetName();

if (is_int($dataSetName)) {
return sprintf(
'%s with data set #%d',
$test->methodName(),
$dataSetName,
);
}

return sprintf(
'%s with data set "%s"',
$test->methodName(),
$dataSetName,
);
}

/**
@psalm-assert


*/
private function createTestCase(Errored|Failed|MarkedIncomplete|PreparationStarted|Prepared|Skipped $event): void
{
$testCase = $this->document->createElement('testcase');

$test = $event->test();
$file = $this->converter->getTestCaseLocation($test); 

$testCase->setAttribute('name', $this->converter->getTestCaseMethodName($test)); 
$testCase->setAttribute('file', $file); 

if ($test->isTestMethod()) {
assert($test instanceof TestMethod);


$className = $this->converter->getTrimmedTestClassName($test); 
$testCase->setAttribute('class', $className); 
$testCase->setAttribute('classname', str_replace('\\', '.', $className)); 
}

$this->currentTestCase = $testCase;
$this->time = $event->telemetryInfo()->time();
}
}
