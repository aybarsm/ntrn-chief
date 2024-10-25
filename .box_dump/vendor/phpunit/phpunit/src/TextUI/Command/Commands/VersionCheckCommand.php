<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Command;

use const PHP_EOL;
use function file_get_contents;
use function sprintf;
use function version_compare;
use PHPUnit\Runner\Version;

/**
@no-named-arguments




*/
final class VersionCheckCommand implements Command
{
public function execute(): Result
{
$latestVersion = file_get_contents('https://phar.phpunit.de/latest-version-of/phpunit');
$latestCompatibleVersion = file_get_contents('https://phar.phpunit.de/latest-version-of/phpunit-' . Version::majorVersionNumber());

$notLatest = version_compare($latestVersion, Version::id(), '>');
$notLatestCompatible = version_compare($latestCompatibleVersion, Version::id(), '>');

if (!$notLatest && !$notLatestCompatible) {
return Result::from(
'You are using the latest version of PHPUnit.' . PHP_EOL,
);
}

$buffer = 'You are not using the latest version of PHPUnit.' . PHP_EOL;

if ($notLatestCompatible) {
$buffer .= sprintf(
'The latest version compatible with PHPUnit %s is PHPUnit %s.' . PHP_EOL,
Version::id(),
$latestCompatibleVersion,
);
}

if ($notLatest) {
$buffer .= sprintf(
'The latest version is PHPUnit %s.' . PHP_EOL,
$latestVersion,
);
}

return Result::from($buffer);
}
}
