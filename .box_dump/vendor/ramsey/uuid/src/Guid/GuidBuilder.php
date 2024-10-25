<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Guid;

use Ramsey\Uuid\Builder\UuidBuilderInterface;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Exception\UnableToBuildUuidException;
use Ramsey\Uuid\UuidInterface;
use Throwable;

/**
@psalm-immutable




*/
class GuidBuilder implements UuidBuilderInterface
{






public function __construct(
private NumberConverterInterface $numberConverter,
private TimeConverterInterface $timeConverter
) {
}

/**
@psalm-pure







*/
public function build(CodecInterface $codec, string $bytes): UuidInterface
{
try {
return new Guid(
$this->buildFields($bytes),
$this->numberConverter,
$codec,
$this->timeConverter
);
} catch (Throwable $e) {
throw new UnableToBuildUuidException($e->getMessage(), (int) $e->getCode(), $e);
}
}




protected function buildFields(string $bytes): Fields
{
return new Fields($bytes);
}
}
