<?php

declare(strict_types=1);










namespace Carbon\Exceptions;

use BadMethodCallException as BaseBadMethodCallException;
use Throwable;

class UnknownMethodException extends BaseBadMethodCallException implements BadMethodCallException
{





protected $method;








public function __construct($method, $code = 0, ?Throwable $previous = null)
{
$this->method = $method;

parent::__construct("Method $method does not exist.", $code, $previous);
}






public function getMethod(): string
{
return $this->method;
}
}
