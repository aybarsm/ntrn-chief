<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Codec;

use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\UuidInterface;

use function bin2hex;
use function sprintf;
use function substr;
use function substr_replace;

/**
@psalm-immutable























*/
class TimestampFirstCombCodec extends StringCodec
{
/**
@psalm-return
@psalm-suppress
@psalm-suppress
*/
public function encode(UuidInterface $uuid): string
{
$bytes = $this->swapBytes($uuid->getFields()->getBytes());

return sprintf(
'%08s-%04s-%04s-%04s-%012s',
bin2hex(substr($bytes, 0, 4)),
bin2hex(substr($bytes, 4, 2)),
bin2hex(substr($bytes, 6, 2)),
bin2hex(substr($bytes, 8, 2)),
bin2hex(substr($bytes, 10))
);
}

/**
@psalm-return
@psalm-suppress
@psalm-suppress
*/
public function encodeBinary(UuidInterface $uuid): string
{
/**
@phpstan-ignore-next-line */
return $this->swapBytes($uuid->getFields()->getBytes());
}






public function decode(string $encodedUuid): UuidInterface
{
$bytes = $this->getBytes($encodedUuid);

return $this->getBuilder()->build($this, $this->swapBytes($bytes));
}

public function decodeBytes(string $bytes): UuidInterface
{
return $this->getBuilder()->build($this, $this->swapBytes($bytes));
}




private function swapBytes(string $bytes): string
{
$first48Bits = substr($bytes, 0, 6);
$last48Bits = substr($bytes, -6);

$bytes = substr_replace($bytes, $last48Bits, 0, 6);
$bytes = substr_replace($bytes, $first48Bits, -6);

return $bytes;
}
}
