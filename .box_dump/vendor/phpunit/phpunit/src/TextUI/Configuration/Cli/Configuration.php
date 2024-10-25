<?php declare(strict_types=1);








namespace PHPUnit\TextUI\CliArguments;

/**
@no-named-arguments
@psalm-immutable



*/
final class Configuration
{
/**
@psalm-var
*/
private readonly array $arguments;
private readonly ?string $atLeastVersion;
private readonly ?bool $backupGlobals;
private readonly ?bool $backupStaticProperties;
private readonly ?bool $beStrictAboutChangesToGlobalState;
private readonly ?string $bootstrap;
private readonly ?string $cacheDirectory;
private readonly ?bool $cacheResult;
private readonly ?string $cacheResultFile;
private readonly bool $checkVersion;
private readonly ?string $colors;
private readonly null|int|string $columns;
private readonly ?string $configurationFile;
private readonly ?array $coverageFilter;
private readonly ?string $coverageClover;
private readonly ?string $coverageCobertura;
private readonly ?string $coverageCrap4J;
private readonly ?string $coverageHtml;
private readonly ?string $coveragePhp;
private readonly ?string $coverageText;
private readonly ?bool $coverageTextShowUncoveredFiles;
private readonly ?bool $coverageTextShowOnlySummary;
private readonly ?string $coverageXml;
private readonly ?bool $pathCoverage;
private readonly ?string $coverageCacheDirectory;
private readonly bool $warmCoverageCache;
private readonly ?int $defaultTimeLimit;
private readonly ?bool $disableCodeCoverageIgnore;
private readonly ?bool $disallowTestOutput;
private readonly ?bool $enforceTimeLimit;
private readonly ?array $excludeGroups;
private readonly ?int $executionOrder;
private readonly ?int $executionOrderDefects;
private readonly ?bool $failOnDeprecation;
private readonly ?bool $failOnPhpunitDeprecation;
private readonly ?bool $failOnEmptyTestSuite;
private readonly ?bool $failOnIncomplete;
private readonly ?bool $failOnNotice;
private readonly ?bool $failOnRisky;
private readonly ?bool $failOnSkipped;
private readonly ?bool $failOnWarning;
private readonly ?bool $stopOnDefect;
private readonly ?bool $stopOnDeprecation;
private readonly ?bool $stopOnError;
private readonly ?bool $stopOnFailure;
private readonly ?bool $stopOnIncomplete;
private readonly ?bool $stopOnNotice;
private readonly ?bool $stopOnRisky;
private readonly ?bool $stopOnSkipped;
private readonly ?bool $stopOnWarning;
private readonly ?string $filter;
private readonly ?string $generateBaseline;
private readonly ?string $useBaseline;
private readonly bool $ignoreBaseline;
private readonly bool $generateConfiguration;
private readonly bool $migrateConfiguration;
private readonly ?array $groups;
private readonly ?array $testsCovering;
private readonly ?array $testsUsing;
private readonly bool $help;
private readonly ?string $includePath;
private readonly ?array $iniSettings;
private readonly ?string $junitLogfile;
private readonly bool $listGroups;
private readonly bool $listSuites;
private readonly bool $listTests;
private readonly ?string $listTestsXml;
private readonly ?bool $noCoverage;
private readonly ?bool $noExtensions;
private readonly ?bool $noOutput;
private readonly ?bool $noProgress;
private readonly ?bool $noResults;
private readonly ?bool $noLogging;
private readonly ?bool $processIsolation;
private readonly ?int $randomOrderSeed;
private readonly ?bool $reportUselessTests;
private readonly ?bool $resolveDependencies;
private readonly ?bool $reverseList;
private readonly ?bool $stderr;
private readonly ?bool $strictCoverage;
private readonly ?string $teamcityLogfile;
private readonly ?bool $teamCityPrinter;
private readonly ?string $testdoxHtmlFile;
private readonly ?string $testdoxTextFile;
private readonly ?bool $testdoxPrinter;

/**
@psalm-var
*/
private readonly ?array $testSuffixes;
private readonly ?string $testSuite;
private readonly ?string $excludeTestSuite;
private readonly bool $useDefaultConfiguration;
private readonly ?bool $displayDetailsOnIncompleteTests;
private readonly ?bool $displayDetailsOnSkippedTests;
private readonly ?bool $displayDetailsOnTestsThatTriggerDeprecations;
private readonly ?bool $displayDetailsOnPhpunitDeprecations;
private readonly ?bool $displayDetailsOnTestsThatTriggerErrors;
private readonly ?bool $displayDetailsOnTestsThatTriggerNotices;
private readonly ?bool $displayDetailsOnTestsThatTriggerWarnings;
private readonly bool $version;
private readonly ?string $logEventsText;
private readonly ?string $logEventsVerboseText;
private readonly bool $debug;

/**
@psalm-param
@psalm-param
*/
public function __construct(array $arguments, ?string $atLeastVersion, ?bool $backupGlobals, ?bool $backupStaticProperties, ?bool $beStrictAboutChangesToGlobalState, ?string $bootstrap, ?string $cacheDirectory, ?bool $cacheResult, ?string $cacheResultFile, bool $checkVersion, ?string $colors, null|int|string $columns, ?string $configurationFile, ?string $coverageClover, ?string $coverageCobertura, ?string $coverageCrap4J, ?string $coverageHtml, ?string $coveragePhp, ?string $coverageText, ?bool $coverageTextShowUncoveredFiles, ?bool $coverageTextShowOnlySummary, ?string $coverageXml, ?bool $pathCoverage, ?string $coverageCacheDirectory, bool $warmCoverageCache, ?int $defaultTimeLimit, ?bool $disableCodeCoverageIgnore, ?bool $disallowTestOutput, ?bool $enforceTimeLimit, ?array $excludeGroups, ?int $executionOrder, ?int $executionOrderDefects, ?bool $failOnDeprecation, ?bool $failOnPhpunitDeprecation, ?bool $failOnEmptyTestSuite, ?bool $failOnIncomplete, ?bool $failOnNotice, ?bool $failOnRisky, ?bool $failOnSkipped, ?bool $failOnWarning, ?bool $stopOnDefect, ?bool $stopOnDeprecation, ?bool $stopOnError, ?bool $stopOnFailure, ?bool $stopOnIncomplete, ?bool $stopOnNotice, ?bool $stopOnRisky, ?bool $stopOnSkipped, ?bool $stopOnWarning, ?string $filter, ?string $generateBaseline, ?string $useBaseline, bool $ignoreBaseline, bool $generateConfiguration, bool $migrateConfiguration, ?array $groups, ?array $testsCovering, ?array $testsUsing, bool $help, ?string $includePath, ?array $iniSettings, ?string $junitLogfile, bool $listGroups, bool $listSuites, bool $listTests, ?string $listTestsXml, ?bool $noCoverage, ?bool $noExtensions, ?bool $noOutput, ?bool $noProgress, ?bool $noResults, ?bool $noLogging, ?bool $processIsolation, ?int $randomOrderSeed, ?bool $reportUselessTests, ?bool $resolveDependencies, ?bool $reverseList, ?bool $stderr, ?bool $strictCoverage, ?string $teamcityLogfile, ?string $testdoxHtmlFile, ?string $testdoxTextFile, ?array $testSuffixes, ?string $testSuite, ?string $excludeTestSuite, bool $useDefaultConfiguration, ?bool $displayDetailsOnIncompleteTests, ?bool $displayDetailsOnSkippedTests, ?bool $displayDetailsOnTestsThatTriggerDeprecations, ?bool $displayDetailsOnPhpunitDeprecations, ?bool $displayDetailsOnTestsThatTriggerErrors, ?bool $displayDetailsOnTestsThatTriggerNotices, ?bool $displayDetailsOnTestsThatTriggerWarnings, bool $version, ?array $coverageFilter, ?string $logEventsText, ?string $logEventsVerboseText, ?bool $printerTeamCity, ?bool $printerTestDox, bool $debug)
{
$this->arguments = $arguments;
$this->atLeastVersion = $atLeastVersion;
$this->backupGlobals = $backupGlobals;
$this->backupStaticProperties = $backupStaticProperties;
$this->beStrictAboutChangesToGlobalState = $beStrictAboutChangesToGlobalState;
$this->bootstrap = $bootstrap;
$this->cacheDirectory = $cacheDirectory;
$this->cacheResult = $cacheResult;
$this->cacheResultFile = $cacheResultFile;
$this->checkVersion = $checkVersion;
$this->colors = $colors;
$this->columns = $columns;
$this->configurationFile = $configurationFile;
$this->coverageFilter = $coverageFilter;
$this->coverageClover = $coverageClover;
$this->coverageCobertura = $coverageCobertura;
$this->coverageCrap4J = $coverageCrap4J;
$this->coverageHtml = $coverageHtml;
$this->coveragePhp = $coveragePhp;
$this->coverageText = $coverageText;
$this->coverageTextShowUncoveredFiles = $coverageTextShowUncoveredFiles;
$this->coverageTextShowOnlySummary = $coverageTextShowOnlySummary;
$this->coverageXml = $coverageXml;
$this->pathCoverage = $pathCoverage;
$this->coverageCacheDirectory = $coverageCacheDirectory;
$this->warmCoverageCache = $warmCoverageCache;
$this->defaultTimeLimit = $defaultTimeLimit;
$this->disableCodeCoverageIgnore = $disableCodeCoverageIgnore;
$this->disallowTestOutput = $disallowTestOutput;
$this->enforceTimeLimit = $enforceTimeLimit;
$this->excludeGroups = $excludeGroups;
$this->executionOrder = $executionOrder;
$this->executionOrderDefects = $executionOrderDefects;
$this->failOnDeprecation = $failOnDeprecation;
$this->failOnPhpunitDeprecation = $failOnPhpunitDeprecation;
$this->failOnEmptyTestSuite = $failOnEmptyTestSuite;
$this->failOnIncomplete = $failOnIncomplete;
$this->failOnNotice = $failOnNotice;
$this->failOnRisky = $failOnRisky;
$this->failOnSkipped = $failOnSkipped;
$this->failOnWarning = $failOnWarning;
$this->stopOnDefect = $stopOnDefect;
$this->stopOnDeprecation = $stopOnDeprecation;
$this->stopOnError = $stopOnError;
$this->stopOnFailure = $stopOnFailure;
$this->stopOnIncomplete = $stopOnIncomplete;
$this->stopOnNotice = $stopOnNotice;
$this->stopOnRisky = $stopOnRisky;
$this->stopOnSkipped = $stopOnSkipped;
$this->stopOnWarning = $stopOnWarning;
$this->filter = $filter;
$this->generateBaseline = $generateBaseline;
$this->useBaseline = $useBaseline;
$this->ignoreBaseline = $ignoreBaseline;
$this->generateConfiguration = $generateConfiguration;
$this->migrateConfiguration = $migrateConfiguration;
$this->groups = $groups;
$this->testsCovering = $testsCovering;
$this->testsUsing = $testsUsing;
$this->help = $help;
$this->includePath = $includePath;
$this->iniSettings = $iniSettings;
$this->junitLogfile = $junitLogfile;
$this->listGroups = $listGroups;
$this->listSuites = $listSuites;
$this->listTests = $listTests;
$this->listTestsXml = $listTestsXml;
$this->noCoverage = $noCoverage;
$this->noExtensions = $noExtensions;
$this->noOutput = $noOutput;
$this->noProgress = $noProgress;
$this->noResults = $noResults;
$this->noLogging = $noLogging;
$this->processIsolation = $processIsolation;
$this->randomOrderSeed = $randomOrderSeed;
$this->reportUselessTests = $reportUselessTests;
$this->resolveDependencies = $resolveDependencies;
$this->reverseList = $reverseList;
$this->stderr = $stderr;
$this->strictCoverage = $strictCoverage;
$this->teamcityLogfile = $teamcityLogfile;
$this->testdoxHtmlFile = $testdoxHtmlFile;
$this->testdoxTextFile = $testdoxTextFile;
$this->testSuffixes = $testSuffixes;
$this->testSuite = $testSuite;
$this->excludeTestSuite = $excludeTestSuite;
$this->useDefaultConfiguration = $useDefaultConfiguration;
$this->displayDetailsOnIncompleteTests = $displayDetailsOnIncompleteTests;
$this->displayDetailsOnSkippedTests = $displayDetailsOnSkippedTests;
$this->displayDetailsOnTestsThatTriggerDeprecations = $displayDetailsOnTestsThatTriggerDeprecations;
$this->displayDetailsOnPhpunitDeprecations = $displayDetailsOnPhpunitDeprecations;
$this->displayDetailsOnTestsThatTriggerErrors = $displayDetailsOnTestsThatTriggerErrors;
$this->displayDetailsOnTestsThatTriggerNotices = $displayDetailsOnTestsThatTriggerNotices;
$this->displayDetailsOnTestsThatTriggerWarnings = $displayDetailsOnTestsThatTriggerWarnings;
$this->version = $version;
$this->logEventsText = $logEventsText;
$this->logEventsVerboseText = $logEventsVerboseText;
$this->teamCityPrinter = $printerTeamCity;
$this->testdoxPrinter = $printerTestDox;
$this->debug = $debug;
}

/**
@psalm-return
*/
public function arguments(): array
{
return $this->arguments;
}

/**
@psalm-assert-if-true
*/
public function hasAtLeastVersion(): bool
{
return $this->atLeastVersion !== null;
}




public function atLeastVersion(): string
{
if (!$this->hasAtLeastVersion()) {
throw new Exception;
}

return $this->atLeastVersion;
}

/**
@psalm-assert-if-true
*/
public function hasBackupGlobals(): bool
{
return $this->backupGlobals !== null;
}




public function backupGlobals(): bool
{
if (!$this->hasBackupGlobals()) {
throw new Exception;
}

return $this->backupGlobals;
}

/**
@psalm-assert-if-true
*/
public function hasBackupStaticProperties(): bool
{
return $this->backupStaticProperties !== null;
}




public function backupStaticProperties(): bool
{
if (!$this->hasBackupStaticProperties()) {
throw new Exception;
}

return $this->backupStaticProperties;
}

/**
@psalm-assert-if-true
*/
public function hasBeStrictAboutChangesToGlobalState(): bool
{
return $this->beStrictAboutChangesToGlobalState !== null;
}




public function beStrictAboutChangesToGlobalState(): bool
{
if (!$this->hasBeStrictAboutChangesToGlobalState()) {
throw new Exception;
}

return $this->beStrictAboutChangesToGlobalState;
}

/**
@psalm-assert-if-true
*/
public function hasBootstrap(): bool
{
return $this->bootstrap !== null;
}




public function bootstrap(): string
{
if (!$this->hasBootstrap()) {
throw new Exception;
}

return $this->bootstrap;
}

/**
@psalm-assert-if-true
*/
public function hasCacheDirectory(): bool
{
return $this->cacheDirectory !== null;
}




public function cacheDirectory(): string
{
if (!$this->hasCacheDirectory()) {
throw new Exception;
}

return $this->cacheDirectory;
}

/**
@psalm-assert-if-true
*/
public function hasCacheResult(): bool
{
return $this->cacheResult !== null;
}




public function cacheResult(): bool
{
if (!$this->hasCacheResult()) {
throw new Exception;
}

return $this->cacheResult;
}

/**
@psalm-assert-if-true


*/
public function hasCacheResultFile(): bool
{
return $this->cacheResultFile !== null;
}






public function cacheResultFile(): string
{
if (!$this->hasCacheResultFile()) {
throw new Exception;
}

return $this->cacheResultFile;
}

public function checkVersion(): bool
{
return $this->checkVersion;
}

/**
@psalm-assert-if-true
*/
public function hasColors(): bool
{
return $this->colors !== null;
}




public function colors(): string
{
if (!$this->hasColors()) {
throw new Exception;
}

return $this->colors;
}

/**
@psalm-assert-if-true
*/
public function hasColumns(): bool
{
return $this->columns !== null;
}




public function columns(): int|string
{
if (!$this->hasColumns()) {
throw new Exception;
}

return $this->columns;
}

/**
@psalm-assert-if-true
*/
public function hasConfigurationFile(): bool
{
return $this->configurationFile !== null;
}




public function configurationFile(): string
{
if (!$this->hasConfigurationFile()) {
throw new Exception;
}

return $this->configurationFile;
}

/**
@psalm-assert-if-true
*/
public function hasCoverageFilter(): bool
{
return $this->coverageFilter !== null;
}




public function coverageFilter(): array
{
if (!$this->hasCoverageFilter()) {
throw new Exception;
}

return $this->coverageFilter;
}

/**
@psalm-assert-if-true
*/
public function hasCoverageClover(): bool
{
return $this->coverageClover !== null;
}




public function coverageClover(): string
{
if (!$this->hasCoverageClover()) {
throw new Exception;
}

return $this->coverageClover;
}

/**
@psalm-assert-if-true
*/
public function hasCoverageCobertura(): bool
{
return $this->coverageCobertura !== null;
}




public function coverageCobertura(): string
{
if (!$this->hasCoverageCobertura()) {
throw new Exception;
}

return $this->coverageCobertura;
}

/**
@psalm-assert-if-true
*/
public function hasCoverageCrap4J(): bool
{
return $this->coverageCrap4J !== null;
}




public function coverageCrap4J(): string
{
if (!$this->hasCoverageCrap4J()) {
throw new Exception;
}

return $this->coverageCrap4J;
}

/**
@psalm-assert-if-true
*/
public function hasCoverageHtml(): bool
{
return $this->coverageHtml !== null;
}




public function coverageHtml(): string
{
if (!$this->hasCoverageHtml()) {
throw new Exception;
}

return $this->coverageHtml;
}

/**
@psalm-assert-if-true
*/
public function hasCoveragePhp(): bool
{
return $this->coveragePhp !== null;
}




public function coveragePhp(): string
{
if (!$this->hasCoveragePhp()) {
throw new Exception;
}

return $this->coveragePhp;
}

/**
@psalm-assert-if-true
*/
public function hasCoverageText(): bool
{
return $this->coverageText !== null;
}




public function coverageText(): string
{
if (!$this->hasCoverageText()) {
throw new Exception;
}

return $this->coverageText;
}

/**
@psalm-assert-if-true
*/
public function hasCoverageTextShowUncoveredFiles(): bool
{
return $this->coverageTextShowUncoveredFiles !== null;
}




public function coverageTextShowUncoveredFiles(): bool
{
if (!$this->hasCoverageTextShowUncoveredFiles()) {
throw new Exception;
}

return $this->coverageTextShowUncoveredFiles;
}

/**
@psalm-assert-if-true
*/
public function hasCoverageTextShowOnlySummary(): bool
{
return $this->coverageTextShowOnlySummary !== null;
}




public function coverageTextShowOnlySummary(): bool
{
if (!$this->hasCoverageTextShowOnlySummary()) {
throw new Exception;
}

return $this->coverageTextShowOnlySummary;
}

/**
@psalm-assert-if-true
*/
public function hasCoverageXml(): bool
{
return $this->coverageXml !== null;
}




public function coverageXml(): string
{
if (!$this->hasCoverageXml()) {
throw new Exception;
}

return $this->coverageXml;
}

/**
@psalm-assert-if-true
*/
public function hasPathCoverage(): bool
{
return $this->pathCoverage !== null;
}




public function pathCoverage(): bool
{
if (!$this->hasPathCoverage()) {
throw new Exception;
}

return $this->pathCoverage;
}

/**
@psalm-assert-if-true


*/
public function hasCoverageCacheDirectory(): bool
{
return $this->coverageCacheDirectory !== null;
}






public function coverageCacheDirectory(): string
{
if (!$this->hasCoverageCacheDirectory()) {
throw new Exception;
}

return $this->coverageCacheDirectory;
}

public function warmCoverageCache(): bool
{
return $this->warmCoverageCache;
}

/**
@psalm-assert-if-true
*/
public function hasDefaultTimeLimit(): bool
{
return $this->defaultTimeLimit !== null;
}




public function defaultTimeLimit(): int
{
if (!$this->hasDefaultTimeLimit()) {
throw new Exception;
}

return $this->defaultTimeLimit;
}

/**
@psalm-assert-if-true
*/
public function hasDisableCodeCoverageIgnore(): bool
{
return $this->disableCodeCoverageIgnore !== null;
}




public function disableCodeCoverageIgnore(): bool
{
if (!$this->hasDisableCodeCoverageIgnore()) {
throw new Exception;
}

return $this->disableCodeCoverageIgnore;
}

/**
@psalm-assert-if-true
*/
public function hasDisallowTestOutput(): bool
{
return $this->disallowTestOutput !== null;
}




public function disallowTestOutput(): bool
{
if (!$this->hasDisallowTestOutput()) {
throw new Exception;
}

return $this->disallowTestOutput;
}

/**
@psalm-assert-if-true
*/
public function hasEnforceTimeLimit(): bool
{
return $this->enforceTimeLimit !== null;
}




public function enforceTimeLimit(): bool
{
if (!$this->hasEnforceTimeLimit()) {
throw new Exception;
}

return $this->enforceTimeLimit;
}

/**
@psalm-assert-if-true
*/
public function hasExcludeGroups(): bool
{
return $this->excludeGroups !== null;
}




public function excludeGroups(): array
{
if (!$this->hasExcludeGroups()) {
throw new Exception;
}

return $this->excludeGroups;
}

/**
@psalm-assert-if-true
*/
public function hasExecutionOrder(): bool
{
return $this->executionOrder !== null;
}




public function executionOrder(): int
{
if (!$this->hasExecutionOrder()) {
throw new Exception;
}

return $this->executionOrder;
}

/**
@psalm-assert-if-true
*/
public function hasExecutionOrderDefects(): bool
{
return $this->executionOrderDefects !== null;
}




public function executionOrderDefects(): int
{
if (!$this->hasExecutionOrderDefects()) {
throw new Exception;
}

return $this->executionOrderDefects;
}

/**
@psalm-assert-if-true
*/
public function hasFailOnDeprecation(): bool
{
return $this->failOnDeprecation !== null;
}




public function failOnDeprecation(): bool
{
if (!$this->hasFailOnDeprecation()) {
throw new Exception;
}

return $this->failOnDeprecation;
}

/**
@psalm-assert-if-true
*/
public function hasFailOnPhpunitDeprecation(): bool
{
return $this->failOnPhpunitDeprecation !== null;
}




public function failOnPhpunitDeprecation(): bool
{
if (!$this->hasFailOnPhpunitDeprecation()) {
throw new Exception;
}

return $this->failOnPhpunitDeprecation;
}

/**
@psalm-assert-if-true
*/
public function hasFailOnEmptyTestSuite(): bool
{
return $this->failOnEmptyTestSuite !== null;
}




public function failOnEmptyTestSuite(): bool
{
if (!$this->hasFailOnEmptyTestSuite()) {
throw new Exception;
}

return $this->failOnEmptyTestSuite;
}

/**
@psalm-assert-if-true
*/
public function hasFailOnIncomplete(): bool
{
return $this->failOnIncomplete !== null;
}




public function failOnIncomplete(): bool
{
if (!$this->hasFailOnIncomplete()) {
throw new Exception;
}

return $this->failOnIncomplete;
}

/**
@psalm-assert-if-true
*/
public function hasFailOnNotice(): bool
{
return $this->failOnNotice !== null;
}




public function failOnNotice(): bool
{
if (!$this->hasFailOnNotice()) {
throw new Exception;
}

return $this->failOnNotice;
}

/**
@psalm-assert-if-true
*/
public function hasFailOnRisky(): bool
{
return $this->failOnRisky !== null;
}




public function failOnRisky(): bool
{
if (!$this->hasFailOnRisky()) {
throw new Exception;
}

return $this->failOnRisky;
}

/**
@psalm-assert-if-true
*/
public function hasFailOnSkipped(): bool
{
return $this->failOnSkipped !== null;
}




public function failOnSkipped(): bool
{
if (!$this->hasFailOnSkipped()) {
throw new Exception;
}

return $this->failOnSkipped;
}

/**
@psalm-assert-if-true
*/
public function hasFailOnWarning(): bool
{
return $this->failOnWarning !== null;
}




public function failOnWarning(): bool
{
if (!$this->hasFailOnWarning()) {
throw new Exception;
}

return $this->failOnWarning;
}

/**
@psalm-assert-if-true
*/
public function hasStopOnDefect(): bool
{
return $this->stopOnDefect !== null;
}




public function stopOnDefect(): bool
{
if (!$this->hasStopOnDefect()) {
throw new Exception;
}

return $this->stopOnDefect;
}

/**
@psalm-assert-if-true
*/
public function hasStopOnDeprecation(): bool
{
return $this->stopOnDeprecation !== null;
}




public function stopOnDeprecation(): bool
{
if (!$this->hasStopOnDeprecation()) {
throw new Exception;
}

return $this->stopOnDeprecation;
}

/**
@psalm-assert-if-true
*/
public function hasStopOnError(): bool
{
return $this->stopOnError !== null;
}




public function stopOnError(): bool
{
if (!$this->hasStopOnError()) {
throw new Exception;
}

return $this->stopOnError;
}

/**
@psalm-assert-if-true
*/
public function hasStopOnFailure(): bool
{
return $this->stopOnFailure !== null;
}




public function stopOnFailure(): bool
{
if (!$this->hasStopOnFailure()) {
throw new Exception;
}

return $this->stopOnFailure;
}

/**
@psalm-assert-if-true
*/
public function hasStopOnIncomplete(): bool
{
return $this->stopOnIncomplete !== null;
}




public function stopOnIncomplete(): bool
{
if (!$this->hasStopOnIncomplete()) {
throw new Exception;
}

return $this->stopOnIncomplete;
}

/**
@psalm-assert-if-true
*/
public function hasStopOnNotice(): bool
{
return $this->stopOnNotice !== null;
}




public function stopOnNotice(): bool
{
if (!$this->hasStopOnNotice()) {
throw new Exception;
}

return $this->stopOnNotice;
}

/**
@psalm-assert-if-true
*/
public function hasStopOnRisky(): bool
{
return $this->stopOnRisky !== null;
}




public function stopOnRisky(): bool
{
if (!$this->hasStopOnRisky()) {
throw new Exception;
}

return $this->stopOnRisky;
}

/**
@psalm-assert-if-true
*/
public function hasStopOnSkipped(): bool
{
return $this->stopOnSkipped !== null;
}




public function stopOnSkipped(): bool
{
if (!$this->hasStopOnSkipped()) {
throw new Exception;
}

return $this->stopOnSkipped;
}

/**
@psalm-assert-if-true
*/
public function hasStopOnWarning(): bool
{
return $this->stopOnWarning !== null;
}




public function stopOnWarning(): bool
{
if (!$this->hasStopOnWarning()) {
throw new Exception;
}

return $this->stopOnWarning;
}

/**
@psalm-assert-if-true
*/
public function hasFilter(): bool
{
return $this->filter !== null;
}




public function filter(): string
{
if (!$this->hasFilter()) {
throw new Exception;
}

return $this->filter;
}

/**
@psalm-assert-if-true
*/
public function hasGenerateBaseline(): bool
{
return $this->generateBaseline !== null;
}




public function generateBaseline(): string
{
if (!$this->hasGenerateBaseline()) {
throw new Exception;
}

return $this->generateBaseline;
}

/**
@psalm-assert-if-true
*/
public function hasUseBaseline(): bool
{
return $this->useBaseline !== null;
}




public function useBaseline(): string
{
if (!$this->hasUseBaseline()) {
throw new Exception;
}

return $this->useBaseline;
}

public function ignoreBaseline(): bool
{
return $this->ignoreBaseline;
}

public function generateConfiguration(): bool
{
return $this->generateConfiguration;
}

public function migrateConfiguration(): bool
{
return $this->migrateConfiguration;
}

/**
@psalm-assert-if-true
*/
public function hasGroups(): bool
{
return $this->groups !== null;
}




public function groups(): array
{
if (!$this->hasGroups()) {
throw new Exception;
}

return $this->groups;
}

/**
@psalm-assert-if-true
*/
public function hasTestsCovering(): bool
{
return $this->testsCovering !== null;
}




public function testsCovering(): array
{
if (!$this->hasTestsCovering()) {
throw new Exception;
}

return $this->testsCovering;
}

/**
@psalm-assert-if-true
*/
public function hasTestsUsing(): bool
{
return $this->testsUsing !== null;
}




public function testsUsing(): array
{
if (!$this->hasTestsUsing()) {
throw new Exception;
}

return $this->testsUsing;
}

public function help(): bool
{
return $this->help;
}

/**
@psalm-assert-if-true
*/
public function hasIncludePath(): bool
{
return $this->includePath !== null;
}




public function includePath(): string
{
if (!$this->hasIncludePath()) {
throw new Exception;
}

return $this->includePath;
}

/**
@psalm-assert-if-true
*/
public function hasIniSettings(): bool
{
return $this->iniSettings !== null;
}




public function iniSettings(): array
{
if (!$this->hasIniSettings()) {
throw new Exception;
}

return $this->iniSettings;
}

/**
@psalm-assert-if-true
*/
public function hasJunitLogfile(): bool
{
return $this->junitLogfile !== null;
}




public function junitLogfile(): string
{
if (!$this->hasJunitLogfile()) {
throw new Exception;
}

return $this->junitLogfile;
}

public function listGroups(): bool
{
return $this->listGroups;
}

public function listSuites(): bool
{
return $this->listSuites;
}

public function listTests(): bool
{
return $this->listTests;
}

/**
@psalm-assert-if-true
*/
public function hasListTestsXml(): bool
{
return $this->listTestsXml !== null;
}




public function listTestsXml(): string
{
if (!$this->hasListTestsXml()) {
throw new Exception;
}

return $this->listTestsXml;
}

/**
@psalm-assert-if-true
*/
public function hasNoCoverage(): bool
{
return $this->noCoverage !== null;
}




public function noCoverage(): bool
{
if (!$this->hasNoCoverage()) {
throw new Exception;
}

return $this->noCoverage;
}

/**
@psalm-assert-if-true
*/
public function hasNoExtensions(): bool
{
return $this->noExtensions !== null;
}




public function noExtensions(): bool
{
if (!$this->hasNoExtensions()) {
throw new Exception;
}

return $this->noExtensions;
}

/**
@psalm-assert-if-true
*/
public function hasNoOutput(): bool
{
return $this->noOutput !== null;
}




public function noOutput(): bool
{
if ($this->noOutput === null) {
throw new Exception;
}

return $this->noOutput;
}

/**
@psalm-assert-if-true
*/
public function hasNoProgress(): bool
{
return $this->noProgress !== null;
}




public function noProgress(): bool
{
if ($this->noProgress === null) {
throw new Exception;
}

return $this->noProgress;
}

/**
@psalm-assert-if-true
*/
public function hasNoResults(): bool
{
return $this->noResults !== null;
}




public function noResults(): bool
{
if ($this->noResults === null) {
throw new Exception;
}

return $this->noResults;
}

/**
@psalm-assert-if-true
*/
public function hasNoLogging(): bool
{
return $this->noLogging !== null;
}




public function noLogging(): bool
{
if (!$this->hasNoLogging()) {
throw new Exception;
}

return $this->noLogging;
}

/**
@psalm-assert-if-true
*/
public function hasProcessIsolation(): bool
{
return $this->processIsolation !== null;
}




public function processIsolation(): bool
{
if (!$this->hasProcessIsolation()) {
throw new Exception;
}

return $this->processIsolation;
}

/**
@psalm-assert-if-true
*/
public function hasRandomOrderSeed(): bool
{
return $this->randomOrderSeed !== null;
}




public function randomOrderSeed(): int
{
if (!$this->hasRandomOrderSeed()) {
throw new Exception;
}

return $this->randomOrderSeed;
}

/**
@psalm-assert-if-true
*/
public function hasReportUselessTests(): bool
{
return $this->reportUselessTests !== null;
}




public function reportUselessTests(): bool
{
if (!$this->hasReportUselessTests()) {
throw new Exception;
}

return $this->reportUselessTests;
}

/**
@psalm-assert-if-true
*/
public function hasResolveDependencies(): bool
{
return $this->resolveDependencies !== null;
}




public function resolveDependencies(): bool
{
if (!$this->hasResolveDependencies()) {
throw new Exception;
}

return $this->resolveDependencies;
}

/**
@psalm-assert-if-true
*/
public function hasReverseList(): bool
{
return $this->reverseList !== null;
}




public function reverseList(): bool
{
if (!$this->hasReverseList()) {
throw new Exception;
}

return $this->reverseList;
}

/**
@psalm-assert-if-true
*/
public function hasStderr(): bool
{
return $this->stderr !== null;
}




public function stderr(): bool
{
if (!$this->hasStderr()) {
throw new Exception;
}

return $this->stderr;
}

/**
@psalm-assert-if-true
*/
public function hasStrictCoverage(): bool
{
return $this->strictCoverage !== null;
}




public function strictCoverage(): bool
{
if (!$this->hasStrictCoverage()) {
throw new Exception;
}

return $this->strictCoverage;
}

/**
@psalm-assert-if-true
*/
public function hasTeamcityLogfile(): bool
{
return $this->teamcityLogfile !== null;
}




public function teamcityLogfile(): string
{
if (!$this->hasTeamcityLogfile()) {
throw new Exception;
}

return $this->teamcityLogfile;
}

/**
@psalm-assert-if-true
*/
public function hasTeamCityPrinter(): bool
{
return $this->teamCityPrinter !== null;
}




public function teamCityPrinter(): bool
{
if (!$this->hasTeamCityPrinter()) {
throw new Exception;
}

return $this->teamCityPrinter;
}

/**
@psalm-assert-if-true
*/
public function hasTestdoxHtmlFile(): bool
{
return $this->testdoxHtmlFile !== null;
}




public function testdoxHtmlFile(): string
{
if (!$this->hasTestdoxHtmlFile()) {
throw new Exception;
}

return $this->testdoxHtmlFile;
}

/**
@psalm-assert-if-true
*/
public function hasTestdoxTextFile(): bool
{
return $this->testdoxTextFile !== null;
}




public function testdoxTextFile(): string
{
if (!$this->hasTestdoxTextFile()) {
throw new Exception;
}

return $this->testdoxTextFile;
}

/**
@psalm-assert-if-true
*/
public function hasTestDoxPrinter(): bool
{
return $this->testdoxPrinter !== null;
}




public function testdoxPrinter(): bool
{
if (!$this->hasTestdoxPrinter()) {
throw new Exception;
}

return $this->testdoxPrinter;
}

/**
@psalm-assert-if-true
*/
public function hasTestSuffixes(): bool
{
return $this->testSuffixes !== null;
}

/**
@psalm-return


*/
public function testSuffixes(): array
{
if (!$this->hasTestSuffixes()) {
throw new Exception;
}

return $this->testSuffixes;
}

/**
@psalm-assert-if-true
*/
public function hasTestSuite(): bool
{
return $this->testSuite !== null;
}




public function testSuite(): string
{
if (!$this->hasTestSuite()) {
throw new Exception;
}

return $this->testSuite;
}

/**
@psalm-assert-if-true
*/
public function hasExcludedTestSuite(): bool
{
return $this->excludeTestSuite !== null;
}




public function excludedTestSuite(): string
{
if (!$this->hasExcludedTestSuite()) {
throw new Exception;
}

return $this->excludeTestSuite;
}

public function useDefaultConfiguration(): bool
{
return $this->useDefaultConfiguration;
}

/**
@psalm-assert-if-true
*/
public function hasDisplayDetailsOnIncompleteTests(): bool
{
return $this->displayDetailsOnIncompleteTests !== null;
}




public function displayDetailsOnIncompleteTests(): bool
{
if (!$this->hasDisplayDetailsOnIncompleteTests()) {
throw new Exception;
}

return $this->displayDetailsOnIncompleteTests;
}

/**
@psalm-assert-if-true
*/
public function hasDisplayDetailsOnSkippedTests(): bool
{
return $this->displayDetailsOnSkippedTests !== null;
}




public function displayDetailsOnSkippedTests(): bool
{
if (!$this->hasDisplayDetailsOnSkippedTests()) {
throw new Exception;
}

return $this->displayDetailsOnSkippedTests;
}

/**
@psalm-assert-if-true
*/
public function hasDisplayDetailsOnTestsThatTriggerDeprecations(): bool
{
return $this->displayDetailsOnTestsThatTriggerDeprecations !== null;
}




public function displayDetailsOnTestsThatTriggerDeprecations(): bool
{
if (!$this->hasDisplayDetailsOnTestsThatTriggerDeprecations()) {
throw new Exception;
}

return $this->displayDetailsOnTestsThatTriggerDeprecations;
}

/**
@psalm-assert-if-true
*/
public function hasDisplayDetailsOnPhpunitDeprecations(): bool
{
return $this->displayDetailsOnPhpunitDeprecations !== null;
}




public function displayDetailsOnPhpunitDeprecations(): bool
{
if (!$this->hasDisplayDetailsOnPhpunitDeprecations()) {
throw new Exception;
}

return $this->displayDetailsOnPhpunitDeprecations;
}

/**
@psalm-assert-if-true
*/
public function hasDisplayDetailsOnTestsThatTriggerErrors(): bool
{
return $this->displayDetailsOnTestsThatTriggerErrors !== null;
}




public function displayDetailsOnTestsThatTriggerErrors(): bool
{
if (!$this->hasDisplayDetailsOnTestsThatTriggerErrors()) {
throw new Exception;
}

return $this->displayDetailsOnTestsThatTriggerErrors;
}

/**
@psalm-assert-if-true
*/
public function hasDisplayDetailsOnTestsThatTriggerNotices(): bool
{
return $this->displayDetailsOnTestsThatTriggerNotices !== null;
}




public function displayDetailsOnTestsThatTriggerNotices(): bool
{
if (!$this->hasDisplayDetailsOnTestsThatTriggerNotices()) {
throw new Exception;
}

return $this->displayDetailsOnTestsThatTriggerNotices;
}

/**
@psalm-assert-if-true
*/
public function hasDisplayDetailsOnTestsThatTriggerWarnings(): bool
{
return $this->displayDetailsOnTestsThatTriggerWarnings !== null;
}




public function displayDetailsOnTestsThatTriggerWarnings(): bool
{
if (!$this->hasDisplayDetailsOnTestsThatTriggerWarnings()) {
throw new Exception;
}

return $this->displayDetailsOnTestsThatTriggerWarnings;
}

public function version(): bool
{
return $this->version;
}

/**
@psalm-assert-if-true
*/
public function hasLogEventsText(): bool
{
return $this->logEventsText !== null;
}




public function logEventsText(): string
{
if (!$this->hasLogEventsText()) {
throw new Exception;
}

return $this->logEventsText;
}

/**
@psalm-assert-if-true
*/
public function hasLogEventsVerboseText(): bool
{
return $this->logEventsVerboseText !== null;
}




public function logEventsVerboseText(): string
{
if (!$this->hasLogEventsVerboseText()) {
throw new Exception;
}

return $this->logEventsVerboseText;
}

public function debug(): bool
{
return $this->debug;
}
}
