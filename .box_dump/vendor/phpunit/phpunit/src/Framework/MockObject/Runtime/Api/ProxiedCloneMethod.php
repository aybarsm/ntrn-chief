<?php declare(strict_types=1);








namespace PHPUnit\Framework\MockObject;

/**
@no-named-arguments


*/
trait ProxiedCloneMethod
{
public function __clone(): void
{
$this->__phpunit_invocationMocker = clone $this->__phpunit_getInvocationHandler();

parent::__clone();
}
}
