<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Exception;

use LogicException as PhpLogicException;




class UnsupportedOperationException extends PhpLogicException implements UuidExceptionInterface
{
}
