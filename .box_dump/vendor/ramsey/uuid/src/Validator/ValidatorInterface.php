<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Validator;

/**
@psalm-immutable


*/
interface ValidatorInterface
{
/**
@psalm-return




*/
public function getPattern(): string;








public function validate(string $uuid): bool;
}
