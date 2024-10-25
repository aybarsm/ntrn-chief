<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Codec;

use Ramsey\Uuid\Guid\Guid;
use Ramsey\Uuid\UuidInterface;

use function bin2hex;
use function sprintf;
use function substr;

/**
@psalm-immutable




*/
class GuidStringCodec extends StringCodec
{
public function encode(UuidInterface $uuid): string
{
$hex = bin2hex($uuid->getFields()->getBytes());


return sprintf(
'%02s%02s%02s%02s-%02s%02s-%02s%02s-%04s-%012s',
substr($hex, 6, 2),
substr($hex, 4, 2),
substr($hex, 2, 2),
substr($hex, 0, 2),
substr($hex, 10, 2),
substr($hex, 8, 2),
substr($hex, 14, 2),
substr($hex, 12, 2),
substr($hex, 16, 4),
substr($hex, 20),
);
}

public function decode(string $encodedUuid): UuidInterface
{
$bytes = $this->getBytes($encodedUuid);

return $this->getBuilder()->build($this, $this->swapBytes($bytes));
}

public function decodeBytes(string $bytes): UuidInterface
{

return parent::decode(bin2hex($bytes));
}




private function swapBytes(string $bytes): string
{
return $bytes[3] . $bytes[2] . $bytes[1] . $bytes[0]
. $bytes[5] . $bytes[4]
. $bytes[7] . $bytes[6]
. substr($bytes, 8);
}
}
