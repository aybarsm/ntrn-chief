<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

use const PHP_EOL;
use function assert;
use function count;
use function is_dir;
use function is_file;
use function realpath;
use function str_ends_with;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Exception;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\TextUI\RuntimeException;
use PHPUnit\TextUI\TestDirectoryNotFoundException;
use PHPUnit\TextUI\TestFileNotFoundException;
use PHPUnit\TextUI\XmlConfiguration\TestSuiteMapper;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;

/**
@no-named-arguments


*/
final class TestSuiteBuilder
{






public function build(Configuration $configuration): TestSuite
{
if ($configuration->hasCliArguments()) {
$arguments = [];

foreach ($configuration->cliArguments() as $cliArgument) {
$argument = realpath($cliArgument);

if (!$argument) {
throw new TestFileNotFoundException($cliArgument);
}

$arguments[] = $argument;
}

if (count($arguments) === 1) {
$testSuite = $this->testSuiteFromPath(
$arguments[0],
$configuration->testSuffixes(),
);
} else {
$testSuite = $this->testSuiteFromPathList(
$arguments,
$configuration->testSuffixes(),
);
}
}

if (!isset($testSuite)) {
$xmlConfigurationFile = $configuration->hasConfigurationFile() ? $configuration->configurationFile() : 'Root Test Suite';

assert(!empty($xmlConfigurationFile));

$testSuite = (new TestSuiteMapper)->map(
$xmlConfigurationFile,
$configuration->testSuite(),
$configuration->includeTestSuite(),
$configuration->excludeTestSuite(),
);
}

EventFacade::emitter()->testSuiteLoaded(\PHPUnit\Event\TestSuite\TestSuiteBuilder::from($testSuite));

return $testSuite;
}

/**
@psalm-param
@psalm-param
@psalm-param


*/
private function testSuiteFromPath(string $path, array $suffixes, ?TestSuite $suite = null): TestSuite
{
if (str_ends_with($path, '.phpt') && is_file($path)) {
$suite = $suite ?: TestSuite::empty($path);
$suite->addTestFile($path);

return $suite;
}

if (is_dir($path)) {
$files = (new FileIteratorFacade)->getFilesAsArray($path, $suffixes);

$suite = $suite ?: TestSuite::empty('CLI Arguments');
$suite->addTestFiles($files);

return $suite;
}

try {
$testClass = (new TestSuiteLoader)->load($path);
} catch (Exception $e) {
print $e->getMessage() . PHP_EOL;

exit(1);
}

if (!$suite) {
return TestSuite::fromClassReflector($testClass);
}

$suite->addTestSuite($testClass);

return $suite;
}

/**
@psalm-param
@psalm-param


*/
private function testSuiteFromPathList(array $paths, array $suffixes): TestSuite
{
$suite = TestSuite::empty('CLI Arguments');

foreach ($paths as $path) {
$this->testSuiteFromPath($path, $suffixes, $suite);
}

return $suite;
}
}
