<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Codec;

use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
use Ramsey\Uuid\Rfc4122\FieldsInterface as Rfc4122FieldsInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use function strlen;
use function substr;

/**
@psalm-immutable

















*/
class OrderedTimeCodec extends StringCodec
{
/**
@psalm-return
@psalm-suppress
@psalm-suppress




*/
public function encodeBinary(UuidInterface $uuid): string
{
if (
!($uuid->getFields() instanceof Rfc4122FieldsInterface)
|| $uuid->getFields()->getVersion() !== Uuid::UUID_TYPE_TIME
) {
throw new InvalidArgumentException(
'Expected RFC 4122 version 1 (time-based) UUID'
);
}

$bytes = $uuid->getFields()->getBytes();

/**
@phpstan-ignore-next-line */
return $bytes[6] . $bytes[7]
. $bytes[4] . $bytes[5]
. $bytes[0] . $bytes[1] . $bytes[2] . $bytes[3]
. substr($bytes, 8);
}









public function decodeBytes(string $bytes): UuidInterface
{
if (strlen($bytes) !== 16) {
throw new InvalidArgumentException(
'$bytes string should contain 16 characters.'
);
}


$rearrangedBytes = $bytes[4] . $bytes[5] . $bytes[6] . $bytes[7]
. $bytes[2] . $bytes[3]
. $bytes[0] . $bytes[1]
. substr($bytes, 8);

$uuid = parent::decodeBytes($rearrangedBytes);

if (
!($uuid->getFields() instanceof Rfc4122FieldsInterface)
|| $uuid->getFields()->getVersion() !== Uuid::UUID_TYPE_TIME
) {
throw new UnsupportedOperationException(
'Attempting to decode a non-time-based UUID using '
. 'OrderedTimeCodec'
);
}

return $uuid;
}
}
