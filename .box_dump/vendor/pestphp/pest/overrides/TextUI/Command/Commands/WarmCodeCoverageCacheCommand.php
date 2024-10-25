<?php

































declare(strict_types=1);










namespace PHPUnit\TextUI\Command;

use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\NoCoverageCacheDirectoryException;
use SebastianBergmann\CodeCoverage\StaticAnalysis\CacheWarmer;
use SebastianBergmann\Timer\NoActiveTimerException;
use SebastianBergmann\Timer\Timer;




final class WarmCodeCoverageCacheCommand implements Command
{
private readonly Configuration $configuration;

private readonly CodeCoverageFilterRegistry $codeCoverageFilterRegistry;

public function __construct(Configuration $configuration, CodeCoverageFilterRegistry $codeCoverageFilterRegistry)
{
$this->configuration = $configuration;
$this->codeCoverageFilterRegistry = $codeCoverageFilterRegistry;
}





public function execute(): Result
{
if (! $this->configuration->hasCoverageCacheDirectory()) {
return Result::from(
'Cache for static analysis has not been configured'.PHP_EOL,
Result::FAILURE
);
}

$this->codeCoverageFilterRegistry->init($this->configuration);

if (! $this->codeCoverageFilterRegistry->configured()) {
return Result::from(
'Filter for code coverage has not been configured'.PHP_EOL,
Result::FAILURE
);
}

$timer = new Timer;
$timer->start();

(new CacheWarmer)->warmCache(
$this->configuration->coverageCacheDirectory(),
! $this->configuration->disableCodeCoverageIgnore(),
$this->configuration->ignoreDeprecatedCodeUnitsFromCodeCoverage(),
$this->codeCoverageFilterRegistry->get()
);

return Result::from();
}
}
