<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Type;

use JsonSerializable;
use Serializable;

/**
@psalm-immutable


*/
interface TypeInterface extends JsonSerializable, Serializable
{
public function toString(): string;

public function __toString(): string;
}
