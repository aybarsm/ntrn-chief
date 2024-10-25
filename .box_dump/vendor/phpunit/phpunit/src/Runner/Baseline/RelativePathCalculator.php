<?php declare(strict_types=1);








namespace PHPUnit\Runner\Baseline;

use function array_fill;
use function array_merge;
use function array_slice;
use function assert;
use function count;
use function explode;
use function implode;
use function str_replace;
use function strpos;
use function substr;
use function trim;

/**
@no-named-arguments




*/
final class RelativePathCalculator
{
/**
@psalm-var
*/
private readonly string $baselineDirectory;

/**
@psalm-param
*/
public function __construct(string $baselineDirectory)
{
$this->baselineDirectory = $baselineDirectory;
}

/**
@psalm-param
@psalm-return

*/
public function calculate(string $filename): string
{
$result = implode('/', $this->parts($filename));

assert($result !== '');

return $result;
}

/**
@psalm-param
@psalm-return

*/
public function parts(string $filename): array
{
$schemePosition = strpos($filename, '://');

if ($schemePosition !== false) {
$filename = substr($filename, $schemePosition + 3);

assert($filename !== '');
}

$parentParts = explode('/', trim(str_replace('\\', '/', $this->baselineDirectory), '/'));
$parentPartsCount = count($parentParts);
$filenameParts = explode('/', trim(str_replace('\\', '/', $filename), '/'));
$filenamePartsCount = count($filenameParts);

$i = 0;

for (; $i < $filenamePartsCount; $i++) {
if ($parentPartsCount < $i + 1) {
break;
}

$parentPath = implode('/', array_slice($parentParts, 0, $i + 1));
$filenamePath = implode('/', array_slice($filenameParts, 0, $i + 1));

if ($parentPath !== $filenamePath) {
break;
}
}

if ($i === 0) {
return [$filename];
}

$dotsCount = $parentPartsCount - $i;

assert($dotsCount >= 0);

return array_merge(array_fill(0, $dotsCount, '..'), array_slice($filenameParts, $i));
}
}
