<?php declare(strict_types=1);








namespace PHPUnit\TextUI\XmlConfiguration;

use function str_replace;

/**
@no-named-arguments


*/
final class Generator
{



private const TEMPLATE = <<<'EOT'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/{phpunit_version}/phpunit.xsd"
         bootstrap="{bootstrap_script}"
         cacheDirectory="{cache_directory}"
         executionOrder="depends,defects"
         requireCoverageMetadata="true"
         beStrictAboutCoverageMetadata="true"
         beStrictAboutOutputDuringTests="true"
         displayDetailsOnPhpunitDeprecations="true"
         failOnPhpunitDeprecation="true"
         failOnRisky="true"
         failOnWarning="true">
    <testsuites>
        <testsuite name="default">
            <directory>{tests_directory}</directory>
        </testsuite>
    </testsuites>

    <source restrictDeprecations="true" restrictNotices="true" restrictWarnings="true">
        <include>
            <directory>{src_directory}</directory>
        </include>
    </source>
</phpunit>

EOT;

public function generateDefaultConfiguration(string $phpunitVersion, string $bootstrapScript, string $testsDirectory, string $srcDirectory, string $cacheDirectory): string
{
return str_replace(
[
'{phpunit_version}',
'{bootstrap_script}',
'{tests_directory}',
'{src_directory}',
'{cache_directory}',
],
[
$phpunitVersion,
$bootstrapScript,
$testsDirectory,
$srcDirectory,
$cacheDirectory,
],
self::TEMPLATE,
);
}
}