<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Nonstandard;

use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Fields\SerializableFieldsTrait;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Rfc4122\VariantTrait;
use Ramsey\Uuid\Type\Hexadecimal;

use function bin2hex;
use function dechex;
use function hexdec;
use function sprintf;
use function str_pad;
use function strlen;
use function substr;

use const STR_PAD_LEFT;

/**
@psalm-immutable









*/
final class Fields implements FieldsInterface
{
use SerializableFieldsTrait;
use VariantTrait;






public function __construct(private string $bytes)
{
if (strlen($this->bytes) !== 16) {
throw new InvalidArgumentException(
'The byte string must be 16 bytes long; '
. 'received ' . strlen($this->bytes) . ' bytes'
);
}
}

public function getBytes(): string
{
return $this->bytes;
}

public function getClockSeq(): Hexadecimal
{
$clockSeq = hexdec(bin2hex(substr($this->bytes, 8, 2))) & 0x3fff;

return new Hexadecimal(str_pad(dechex($clockSeq), 4, '0', STR_PAD_LEFT));
}

public function getClockSeqHiAndReserved(): Hexadecimal
{
return new Hexadecimal(bin2hex(substr($this->bytes, 8, 1)));
}

public function getClockSeqLow(): Hexadecimal
{
return new Hexadecimal(bin2hex(substr($this->bytes, 9, 1)));
}

public function getNode(): Hexadecimal
{
return new Hexadecimal(bin2hex(substr($this->bytes, 10)));
}

public function getTimeHiAndVersion(): Hexadecimal
{
return new Hexadecimal(bin2hex(substr($this->bytes, 6, 2)));
}

public function getTimeLow(): Hexadecimal
{
return new Hexadecimal(bin2hex(substr($this->bytes, 0, 4)));
}

public function getTimeMid(): Hexadecimal
{
return new Hexadecimal(bin2hex(substr($this->bytes, 4, 2)));
}

public function getTimestamp(): Hexadecimal
{
return new Hexadecimal(sprintf(
'%03x%04s%08s',
hexdec($this->getTimeHiAndVersion()->toString()) & 0x0fff,
$this->getTimeMid()->toString(),
$this->getTimeLow()->toString()
));
}

public function getVersion(): ?int
{
return null;
}

public function isNil(): bool
{
return false;
}

public function isMax(): bool
{
return false;
}
}
