<?php

declare(strict_types=1);

namespace League\Flysystem;

use DateTimeInterface;









interface FilesystemReader
{
public const LIST_SHALLOW = false;
public const LIST_DEEP = true;





public function fileExists(string $location): bool;





public function directoryExists(string $location): bool;





public function has(string $location): bool;





public function read(string $location): string;







public function readStream(string $location);







public function listContents(string $location, bool $deep = self::LIST_SHALLOW): DirectoryListing;





public function lastModified(string $path): int;





public function fileSize(string $path): int;





public function mimeType(string $path): string;





public function visibility(string $path): string;
}
