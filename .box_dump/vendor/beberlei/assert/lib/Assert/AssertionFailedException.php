<?php













namespace Assert;

use Throwable;

interface AssertionFailedException extends Throwable
{



public function getPropertyPath();




public function getValue();

public function getConstraints(): array;
}
