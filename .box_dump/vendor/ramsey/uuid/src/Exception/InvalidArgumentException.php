<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Exception;

use InvalidArgumentException as PhpInvalidArgumentException;




class InvalidArgumentException extends PhpInvalidArgumentException implements UuidExceptionInterface
{
}
