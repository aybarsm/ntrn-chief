<?php declare(strict_types=1);








namespace PHPUnit\Runner\Baseline;

use const FILE_IGNORE_NEW_LINES;
use function assert;
use function file;
use function is_file;
use function sha1;
use PHPUnit\Runner\FileDoesNotExistException;

/**
@no-named-arguments


*/
final class Issue
{
/**
@psalm-var
*/
private readonly string $file;

/**
@psalm-var
*/
private readonly int $line;

/**
@psalm-var
*/
private readonly string $hash;

/**
@psalm-var
*/
private readonly string $description;

/**
@psalm-param
@psalm-param
@psalm-param
@psalm-param



*/
public static function from(string $file, int $line, ?string $hash, string $description): self
{
if ($hash === null) {
$hash = self::calculateHash($file, $line);
}

return new self($file, $line, $hash, $description);
}

/**
@psalm-param
@psalm-param
@psalm-param
@psalm-param
*/
private function __construct(string $file, int $line, string $hash, string $description)
{
$this->file = $file;
$this->line = $line;
$this->hash = $hash;
$this->description = $description;
}

/**
@psalm-return
*/
public function file(): string
{
return $this->file;
}

/**
@psalm-return
*/
public function line(): int
{
return $this->line;
}

/**
@psalm-return
*/
public function hash(): string
{
return $this->hash;
}

/**
@psalm-return
*/
public function description(): string
{
return $this->description;
}

public function equals(self $other): bool
{
return $this->file() === $other->file() &&
$this->line() === $other->line() &&
$this->hash() === $other->hash() &&
$this->description() === $other->description();
}

/**
@psalm-param
@psalm-param
@psalm-return




*/
private static function calculateHash(string $file, int $line): string
{
$lines = @file($file, FILE_IGNORE_NEW_LINES);

if ($lines === false && !is_file($file)) {
throw new FileDoesNotExistException($file);
}

$key = $line - 1;

if (!isset($lines[$key])) {
throw new FileDoesNotHaveLineException($file, $line);
}

$hash = sha1($lines[$key]);

assert($hash !== '');

return $hash;
}
}
