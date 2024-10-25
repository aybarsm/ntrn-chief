<?php

declare(strict_types=1);










namespace Carbon\Exceptions;

use Carbon\CarbonInterface;
use InvalidArgumentException as BaseInvalidArgumentException;
use Throwable;

class NotACarbonClassException extends BaseInvalidArgumentException implements InvalidArgumentException
{





protected $className;








public function __construct($className, $code = 0, ?Throwable $previous = null)
{
$this->className = $className;

parent::__construct(\sprintf(
'Given class does not implement %s: %s',
CarbonInterface::class,
$className,
), $code, $previous);
}






public function getClassName(): string
{
return $this->className;
}
}
