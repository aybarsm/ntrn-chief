<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Builder;

use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\UuidInterface;

/**
@psalm-immutable


*/
interface UuidBuilderInterface
{
/**
@psalm-pure








*/
public function build(CodecInterface $codec, string $bytes): UuidInterface;
}
