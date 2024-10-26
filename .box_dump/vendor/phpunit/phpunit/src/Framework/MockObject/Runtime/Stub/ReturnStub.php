<?php declare(strict_types=1);








namespace PHPUnit\Framework\MockObject\Stub;

use PHPUnit\Framework\MockObject\Invocation;

/**
@no-named-arguments


*/
final class ReturnStub implements Stub
{
private readonly mixed $value;

public function __construct(mixed $value)
{
$this->value = $value;
}

public function invoke(Invocation $invocation): mixed
{
return $this->value;
}
}
