<?php declare(strict_types=1);








namespace SebastianBergmann\FileIterator;

use const GLOB_ONLYDIR;
use function array_filter;
use function array_map;
use function array_merge;
use function array_values;
use function glob;
use function is_dir;
use function is_string;
use function realpath;
use AppendIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;




final class Factory
{
/**
@psalm-param
@psalm-param
@psalm-param
@psalm-param
*/
public function getFileIterator(array|string $paths, array|string $suffixes = '', array|string $prefixes = '', array $exclude = []): AppendIterator
{
if (is_string($paths)) {
$paths = [$paths];
}

$paths = $this->resolveWildcards($paths);
$exclude = $this->resolveWildcards($exclude);

if (is_string($prefixes)) {
if ($prefixes !== '') {
$prefixes = [$prefixes];
} else {
$prefixes = [];
}
}

if (is_string($suffixes)) {
if ($suffixes !== '') {
$suffixes = [$suffixes];
} else {
$suffixes = [];
}
}

$iterator = new AppendIterator;

foreach ($paths as $path) {
if (is_dir($path)) {
$iterator->append(
new Iterator(
$path,
new RecursiveIteratorIterator(
new ExcludeIterator(
new RecursiveDirectoryIterator($path, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::SKIP_DOTS),
$exclude,
),
),
$suffixes,
$prefixes,
)
);
}
}

return $iterator;
}

/**
@psalm-param
@psalm-return

*/
private function resolveWildcards(array $paths): array
{
$_paths = [[]];

foreach ($paths as $path) {
if ($locals = glob($path, GLOB_ONLYDIR)) {
$_paths[] = array_map('\realpath', $locals);
} else {

$_paths[] = [realpath($path)];

}
}

return array_values(array_filter(array_merge(...$_paths)));
}
}
