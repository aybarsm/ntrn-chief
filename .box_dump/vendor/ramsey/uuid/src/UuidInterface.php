<?php











declare(strict_types=1);

namespace Ramsey\Uuid;

use JsonSerializable;
use Ramsey\Uuid\Fields\FieldsInterface;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Serializable;
use Stringable;

/**
@psalm-immutable



*/
interface UuidInterface extends
DeprecatedUuidInterface,
JsonSerializable,
Serializable,
Stringable
{















public function compareTo(UuidInterface $other): int;












public function equals(?object $other): bool;

/**
@psalm-return


*/
public function getBytes(): string;




public function getFields(): FieldsInterface;




public function getHex(): Hexadecimal;




public function getInteger(): IntegerObject;







public function getUrn(): string;

/**
@psalm-return


*/
public function toString(): string;

/**
@psalm-return


*/
public function __toString(): string;
}
