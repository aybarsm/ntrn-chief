<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Rfc4122;

use Ramsey\Uuid\Builder\UuidBuilderInterface;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Converter\Time\UnixTimeConverter;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Exception\UnableToBuildUuidException;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
use Ramsey\Uuid\Math\BrickMathCalculator;
use Ramsey\Uuid\Rfc4122\UuidInterface as Rfc4122UuidInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

/**
@psalm-immutable


*/
class UuidBuilder implements UuidBuilderInterface
{
private TimeConverterInterface $unixTimeConverter;













public function __construct(
private NumberConverterInterface $numberConverter,
private TimeConverterInterface $timeConverter,
?TimeConverterInterface $unixTimeConverter = null
) {
$this->unixTimeConverter = $unixTimeConverter ?? new UnixTimeConverter(new BrickMathCalculator());
}

/**
@psalm-pure







*/
public function build(CodecInterface $codec, string $bytes): UuidInterface
{
try {

$fields = $this->buildFields($bytes);

if ($fields->isNil()) {
return new NilUuid($fields, $this->numberConverter, $codec, $this->timeConverter);
}

if ($fields->isMax()) {
return new MaxUuid($fields, $this->numberConverter, $codec, $this->timeConverter);
}

switch ($fields->getVersion()) {
case Uuid::UUID_TYPE_TIME:
return new UuidV1($fields, $this->numberConverter, $codec, $this->timeConverter);
case Uuid::UUID_TYPE_DCE_SECURITY:
return new UuidV2($fields, $this->numberConverter, $codec, $this->timeConverter);
case Uuid::UUID_TYPE_HASH_MD5:
return new UuidV3($fields, $this->numberConverter, $codec, $this->timeConverter);
case Uuid::UUID_TYPE_RANDOM:
return new UuidV4($fields, $this->numberConverter, $codec, $this->timeConverter);
case Uuid::UUID_TYPE_HASH_SHA1:
return new UuidV5($fields, $this->numberConverter, $codec, $this->timeConverter);
case Uuid::UUID_TYPE_REORDERED_TIME:
return new UuidV6($fields, $this->numberConverter, $codec, $this->timeConverter);
case Uuid::UUID_TYPE_UNIX_TIME:
return new UuidV7($fields, $this->numberConverter, $codec, $this->unixTimeConverter);
case Uuid::UUID_TYPE_CUSTOM:
return new UuidV8($fields, $this->numberConverter, $codec, $this->timeConverter);
}

throw new UnsupportedOperationException(
'The UUID version in the given fields is not supported '
. 'by this UUID builder'
);
} catch (Throwable $e) {
throw new UnableToBuildUuidException($e->getMessage(), (int) $e->getCode(), $e);
}
}




protected function buildFields(string $bytes): FieldsInterface
{
return new Fields($bytes);
}
}
