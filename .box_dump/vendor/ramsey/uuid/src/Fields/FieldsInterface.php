<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Fields;

use Serializable;

/**
@psalm-immutable




*/
interface FieldsInterface extends Serializable
{



public function getBytes(): string;
}
