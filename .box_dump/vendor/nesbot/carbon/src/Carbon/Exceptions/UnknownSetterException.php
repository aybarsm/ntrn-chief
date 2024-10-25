<?php

declare(strict_types=1);










namespace Carbon\Exceptions;

use InvalidArgumentException as BaseInvalidArgumentException;
use Throwable;

class UnknownSetterException extends BaseInvalidArgumentException implements BadMethodCallException
{





protected $setter;








public function __construct($setter, $code = 0, ?Throwable $previous = null)
{
$this->setter = $setter;

parent::__construct("Unknown setter '$setter'", $code, $previous);
}






public function getSetter(): string
{
return $this->setter;
}
}
