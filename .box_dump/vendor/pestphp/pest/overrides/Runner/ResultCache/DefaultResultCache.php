<?php

































declare(strict_types=1);










namespace PHPUnit\Runner\ResultCache;

use const DIRECTORY_SEPARATOR;

use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Runner\DirectoryCannotBeCreatedException;
use PHPUnit\Runner\Exception;
use PHPUnit\Util\Filesystem;

use function array_keys;
use function assert;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function is_dir;
use function json_decode;
use function json_encode;
use function Pest\version;




final class DefaultResultCache implements ResultCache
{



private const DEFAULT_RESULT_CACHE_FILENAME = '.phpunit.result.cache';

private readonly string $cacheFilename;

/**
@psalm-var
*/
private array $defects = [];

/**
@psalm-var
*/
private array $currentDefects = [];

/**
@psalm-var
*/
private array $times = [];

public function __construct(?string $filepath = null)
{
if ($filepath !== null && is_dir($filepath)) {
$filepath .= DIRECTORY_SEPARATOR.self::DEFAULT_RESULT_CACHE_FILENAME;
}

$this->cacheFilename = $filepath ?? $_ENV['PHPUNIT_RESULT_CACHE'] ?? self::DEFAULT_RESULT_CACHE_FILENAME;
}

public function setStatus(string $id, TestStatus $status): void
{
if ($status->isFailure() || $status->isError()) {
$this->currentDefects[$id] = $status;
$this->defects[$id] = $status;
}
}

public function status(string $id): TestStatus
{
return $this->defects[$id] ?? TestStatus::unknown();
}

public function setTime(string $id, float $time): void
{
if (! isset($this->currentDefects[$id])) {
unset($this->defects[$id]);
}

$this->times[$id] = $time;
}

public function time(string $id): float
{
return $this->times[$id] ?? 0.0;
}

public function load(): void
{
$contents = @file_get_contents($this->cacheFilename);

if ($contents === false) {
return;
}

$data = json_decode(
$contents,
true,
);

if ($data === null) {
return;
}

if (! isset($data['version'])) {
return;
}

if ($data['version'] !== $this->cacheVersion()) {
return;
}

assert(isset($data['defects']) && is_array($data['defects']));
assert(isset($data['times']) && is_array($data['times']));

foreach (array_keys($data['defects']) as $test) {
$data['defects'][$test] = TestStatus::from($data['defects'][$test]);
}

$this->defects = $data['defects'];
$this->times = $data['times'];
}




public function persist(): void
{
if (! Filesystem::createDirectory(dirname($this->cacheFilename))) {
throw new DirectoryCannotBeCreatedException($this->cacheFilename);
}

$data = [
'version' => $this->cacheVersion(),
'defects' => [],
'times' => $this->times,
];

foreach ($this->defects as $test => $status) {
$data['defects'][$test] = $status->asInt();
}

file_put_contents(
$this->cacheFilename,
json_encode($data),
LOCK_EX
);
}




private function cacheVersion(): string
{
return 'pest_'.version();
}
}
