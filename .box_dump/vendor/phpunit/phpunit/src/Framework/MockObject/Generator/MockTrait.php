<?php declare(strict_types=1);








namespace PHPUnit\Framework\MockObject\Generator;

use function class_exists;

/**
@no-named-arguments




*/
final class MockTrait implements MockType
{
private readonly string $classCode;

/**
@psalm-var
*/
private readonly string $mockName;

/**
@psalm-param
*/
public function __construct(string $classCode, string $mockName)
{
$this->classCode = $classCode;
$this->mockName = $mockName;
}

/**
@psalm-return
*/
public function generate(): string
{
if (!class_exists($this->mockName, false)) {
eval($this->classCode);
}

return $this->mockName;
}
}
