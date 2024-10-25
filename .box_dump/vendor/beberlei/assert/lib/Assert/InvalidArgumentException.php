<?php













namespace Assert;

class InvalidArgumentException extends \InvalidArgumentException implements AssertionFailedException
{



private $propertyPath;




private $value;




private $constraints;

public function __construct($message, $code, string $propertyPath = null, $value = null, array $constraints = [])
{
parent::__construct($message, $code);

$this->propertyPath = $propertyPath;
$this->value = $value;
$this->constraints = $constraints;
}










public function getPropertyPath()
{
return $this->propertyPath;
}






public function getValue()
{
return $this->value;
}




public function getConstraints(): array
{
return $this->constraints;
}
}
