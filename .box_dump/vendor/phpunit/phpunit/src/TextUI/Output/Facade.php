<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Output;

use const PHP_EOL;
use function assert;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Logging\TeamCity\TeamCityLogger;
use PHPUnit\Logging\TestDox\TestResultCollection;
use PHPUnit\Runner\DirectoryDoesNotExistException;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\CannotOpenSocketException;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\InvalidSocketException;
use PHPUnit\TextUI\Output\Default\ProgressPrinter\ProgressPrinter as DefaultProgressPrinter;
use PHPUnit\TextUI\Output\Default\ResultPrinter as DefaultResultPrinter;
use PHPUnit\TextUI\Output\Default\UnexpectedOutputPrinter;
use PHPUnit\TextUI\Output\TestDox\ResultPrinter as TestDoxResultPrinter;
use SebastianBergmann\Timer\Duration;
use SebastianBergmann\Timer\ResourceUsageFormatter;

/**
@no-named-arguments


*/
final class Facade
{
private static ?Printer $printer = null;
private static ?DefaultResultPrinter $defaultResultPrinter = null;
private static ?TestDoxResultPrinter $testDoxResultPrinter = null;
private static ?SummaryPrinter $summaryPrinter = null;
private static bool $defaultProgressPrinter = false;





public static function init(Configuration $configuration, bool $extensionReplacesProgressOutput, bool $extensionReplacesResultOutput): Printer
{
self::createPrinter($configuration);

assert(self::$printer !== null);

if ($configuration->debug()) {
return self::$printer;
}

self::createUnexpectedOutputPrinter();

if (!$extensionReplacesProgressOutput) {
self::createProgressPrinter($configuration);
}

if (!$extensionReplacesResultOutput) {
self::createResultPrinter($configuration);
self::createSummaryPrinter($configuration);
}

if ($configuration->outputIsTeamCity()) {
new TeamCityLogger(
DefaultPrinter::standardOutput(),
EventFacade::instance(),
);
}

return self::$printer;
}

/**
@psalm-param
*/
public static function printResult(TestResult $result, ?array $testDoxResult, Duration $duration): void
{
assert(self::$printer !== null);

if ($result->numberOfTestsRun() > 0) {
if (self::$defaultProgressPrinter) {
self::$printer->print(PHP_EOL . PHP_EOL);
}

self::$printer->print((new ResourceUsageFormatter)->resourceUsage($duration) . PHP_EOL . PHP_EOL);
}

if (self::$testDoxResultPrinter !== null && $testDoxResult !== null) {
self::$testDoxResultPrinter->print($testDoxResult);
}

if (self::$defaultResultPrinter !== null) {
self::$defaultResultPrinter->print($result);
}

if (self::$summaryPrinter !== null) {
self::$summaryPrinter->print($result);
}
}






public static function printerFor(string $target): Printer
{
if ($target === 'php://stdout') {
if (!self::$printer instanceof NullPrinter) {
return self::$printer;
}

return DefaultPrinter::standardOutput();
}

return DefaultPrinter::from($target);
}

private static function createPrinter(Configuration $configuration): void
{
$printerNeeded = false;

if ($configuration->debug()) {
$printerNeeded = true;
}

if ($configuration->outputIsTeamCity()) {
$printerNeeded = true;
}

if ($configuration->outputIsTestDox()) {
$printerNeeded = true;
}

if (!$configuration->noOutput() && !$configuration->noProgress()) {
$printerNeeded = true;
}

if (!$configuration->noOutput() && !$configuration->noResults()) {
$printerNeeded = true;
}

if ($printerNeeded) {
if ($configuration->outputToStandardErrorStream()) {
self::$printer = DefaultPrinter::standardError();

return;
}

self::$printer = DefaultPrinter::standardOutput();

return;
}

self::$printer = new NullPrinter;
}

private static function createProgressPrinter(Configuration $configuration): void
{
assert(self::$printer !== null);

if (!self::useDefaultProgressPrinter($configuration)) {
return;
}

new DefaultProgressPrinter(
self::$printer,
EventFacade::instance(),
$configuration->colors(),
$configuration->columns(),
$configuration->source(),
);

self::$defaultProgressPrinter = true;
}

private static function useDefaultProgressPrinter(Configuration $configuration): bool
{
if ($configuration->noOutput()) {
return false;
}

if ($configuration->noProgress()) {
return false;
}

if ($configuration->outputIsTeamCity()) {
return false;
}

return true;
}

private static function createResultPrinter(Configuration $configuration): void
{
assert(self::$printer !== null);

if ($configuration->outputIsTestDox()) {
self::$defaultResultPrinter = new DefaultResultPrinter(
self::$printer,
true,
true,
$configuration->displayDetailsOnPhpunitDeprecations(),
false,
false,
true,
false,
false,
$configuration->displayDetailsOnTestsThatTriggerDeprecations(),
$configuration->displayDetailsOnTestsThatTriggerErrors(),
$configuration->displayDetailsOnTestsThatTriggerNotices(),
$configuration->displayDetailsOnTestsThatTriggerWarnings(),
$configuration->reverseDefectList(),
);
}

if ($configuration->outputIsTestDox()) {
self::$testDoxResultPrinter = new TestDoxResultPrinter(
self::$printer,
$configuration->colors(),
);
}

if ($configuration->noOutput() || $configuration->noResults()) {
return;
}

if (self::$defaultResultPrinter !== null) {
return;
}

self::$defaultResultPrinter = new DefaultResultPrinter(
self::$printer,
true,
true,
$configuration->displayDetailsOnPhpunitDeprecations(),
true,
true,
true,
$configuration->displayDetailsOnIncompleteTests(),
$configuration->displayDetailsOnSkippedTests(),
$configuration->displayDetailsOnTestsThatTriggerDeprecations(),
$configuration->displayDetailsOnTestsThatTriggerErrors(),
$configuration->displayDetailsOnTestsThatTriggerNotices(),
$configuration->displayDetailsOnTestsThatTriggerWarnings(),
$configuration->reverseDefectList(),
);
}

private static function createSummaryPrinter(Configuration $configuration): void
{
assert(self::$printer !== null);

if (($configuration->noOutput() || $configuration->noResults()) &&
!($configuration->outputIsTeamCity() || $configuration->outputIsTestDox())) {
return;
}

self::$summaryPrinter = new SummaryPrinter(
self::$printer,
$configuration->colors(),
);
}





private static function createUnexpectedOutputPrinter(): void
{
assert(self::$printer !== null);

new UnexpectedOutputPrinter(self::$printer, EventFacade::instance());
}
}
