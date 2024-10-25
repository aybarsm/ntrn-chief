<?php

declare(strict_types=1);










namespace Carbon\Exceptions;

use InvalidArgumentException as BaseInvalidArgumentException;
use Throwable;




class OutOfRangeException extends BaseInvalidArgumentException implements InvalidArgumentException
{





private $unit;






private $min;






private $max;






private $value;











public function __construct($unit, $min, $max, $value, $code = 0, ?Throwable $previous = null)
{
$this->unit = $unit;
$this->min = $min;
$this->max = $max;
$this->value = $value;

parent::__construct("$unit must be between $min and $max, $value given", $code, $previous);
}




public function getMax()
{
return $this->max;
}




public function getMin()
{
return $this->min;
}




public function getUnit()
{
return $this->unit;
}




public function getValue()
{
return $this->value;
}
}
