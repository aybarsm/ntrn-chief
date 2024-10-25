<?php

































declare(strict_types=1);










namespace PHPUnit\TextUI;

use Pest\Plugins\Only;
use PHPUnit\Event;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\FilterNotConfiguredException;

use function array_map;




final class TestSuiteFilterProcessor
{




public function process(Configuration $configuration, TestSuite $suite): void
{
$factory = new Factory;

if (! $configuration->hasFilter() &&
! $configuration->hasGroups() &&
! $configuration->hasExcludeGroups() &&
! $configuration->hasTestsCovering() &&
! $configuration->hasTestsUsing() &&
! Only::isEnabled()
) {
return;
}

if ($configuration->hasExcludeGroups()) {
$factory->addExcludeGroupFilter(
$configuration->excludeGroups()
);
}

if (Only::isEnabled()) {
$factory->addIncludeGroupFilter(['__pest_only']);
} elseif ($configuration->hasGroups()) {
$factory->addIncludeGroupFilter(
$configuration->groups()
);
}

if ($configuration->hasTestsCovering()) {
$factory->addIncludeGroupFilter(
array_map(
static fn (string $name): string => '__phpunit_covers_'.$name,
$configuration->testsCovering()
)
);
}

if ($configuration->hasTestsUsing()) {
$factory->addIncludeGroupFilter(
array_map(
static fn (string $name): string => '__phpunit_uses_'.$name,
$configuration->testsUsing()
)
);
}

if ($configuration->hasFilter()) {
$factory->addNameFilter(
$configuration->filter()
);
}

$suite->injectFilter($factory);

Event\Facade::emitter()->testSuiteFiltered(
Event\TestSuite\TestSuiteBuilder::from($suite)
);
}
}
