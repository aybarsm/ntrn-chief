<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Rfc4122;

use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Rfc4122\FieldsInterface as Rfc4122FieldsInterface;
use Ramsey\Uuid\Uuid;

/**
@psalm-immutable



*/
final class UuidV3 extends Uuid implements UuidInterface
{











public function __construct(
Rfc4122FieldsInterface $fields,
NumberConverterInterface $numberConverter,
CodecInterface $codec,
TimeConverterInterface $timeConverter
) {
if ($fields->getVersion() !== Uuid::UUID_TYPE_HASH_MD5) {
throw new InvalidArgumentException(
'Fields used to create a UuidV3 must represent a '
. 'version 3 (name-based, MD5-hashed) UUID'
);
}

parent::__construct($fields, $numberConverter, $codec, $timeConverter);
}
}
