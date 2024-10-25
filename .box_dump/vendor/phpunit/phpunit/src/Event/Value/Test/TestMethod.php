<?php declare(strict_types=1);








namespace PHPUnit\Event\Code;

use function assert;
use function is_int;
use function sprintf;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Metadata\MetadataCollection;

/**
@psalm-immutable
@no-named-arguments

*/
final class TestMethod extends Test
{
/**
@psalm-var
*/
private readonly string $className;

/**
@psalm-var
*/
private readonly string $methodName;

/**
@psalm-var
*/
private readonly int $line;
private readonly TestDox $testDox;
private readonly MetadataCollection $metadata;
private readonly TestDataCollection $testData;

/**
@psalm-param
@psalm-param
@psalm-param
@psalm-param
*/
public function __construct(string $className, string $methodName, string $file, int $line, TestDox $testDox, MetadataCollection $metadata, TestDataCollection $testData)
{
parent::__construct($file);

$this->className = $className;
$this->methodName = $methodName;
$this->line = $line;
$this->testDox = $testDox;
$this->metadata = $metadata;
$this->testData = $testData;
}

/**
@psalm-return
*/
public function className(): string
{
return $this->className;
}

/**
@psalm-return
*/
public function methodName(): string
{
return $this->methodName;
}

/**
@psalm-return
*/
public function line(): int
{
return $this->line;
}

public function testDox(): TestDox
{
return $this->testDox;
}

public function metadata(): MetadataCollection
{
return $this->metadata;
}

public function testData(): TestDataCollection
{
return $this->testData;
}

/**
@psalm-assert-if-true
*/
public function isTestMethod(): bool
{
return true;
}

/**
@psalm-return
*/
public function id(): string
{
$buffer = $this->className . '::' . $this->methodName;

if ($this->testData()->hasDataFromDataProvider()) {
$buffer .= '#' . $this->testData->dataFromDataProvider()->dataSetName();
}

return $buffer;
}

/**
@psalm-return
*/
public function nameWithClass(): string
{
return $this->className . '::' . $this->name();
}

/**
@psalm-return
*/
public function name(): string
{
if (!$this->testData->hasDataFromDataProvider()) {
return $this->methodName;
}

$dataSetName = $this->testData->dataFromDataProvider()->dataSetName();

if (is_int($dataSetName)) {
$dataSetName = sprintf(
' with data set #%d',
$dataSetName,
);
} else {
$dataSetName = sprintf(
' with data set "%s"',
$dataSetName,
);
}

return $this->methodName . $dataSetName;
}
}
