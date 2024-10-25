<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Codec;

use Ramsey\Uuid\UuidInterface;

/**
@psalm-immutable


*/
interface CodecInterface
{
/**
@psalm-return







*/
public function encode(UuidInterface $uuid): string;

/**
@psalm-return







*/
public function encodeBinary(UuidInterface $uuid): string;










public function decode(string $encodedUuid): UuidInterface;










public function decodeBytes(string $bytes): UuidInterface;
}
