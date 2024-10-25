<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Rfc4122;

use Ramsey\Uuid\Exception\InvalidBytesException;
use Ramsey\Uuid\Uuid;

use function decbin;
use function str_pad;
use function str_starts_with;
use function strlen;
use function substr;
use function unpack;

use const STR_PAD_LEFT;

/**
@psalm-immutable


*/
trait VariantTrait
{



abstract public function getBytes(): string;















public function getVariant(): int
{
if (strlen($this->getBytes()) !== 16) {
throw new InvalidBytesException('Invalid number of bytes');
}

if ($this->isMax() || $this->isNil()) {


return Uuid::RFC_4122;
}


$parts = unpack('n*', $this->getBytes());






$binary = str_pad(
decbin($parts[5]),
16,
'0',
STR_PAD_LEFT
);

$msb = substr($binary, 0, 3);

if ($msb === '111') {
return Uuid::RESERVED_FUTURE;
} elseif ($msb === '110') {
return Uuid::RESERVED_MICROSOFT;
} elseif (str_starts_with($msb, '10')) {
return Uuid::RFC_4122;
}

return Uuid::RESERVED_NCS;
}
}
