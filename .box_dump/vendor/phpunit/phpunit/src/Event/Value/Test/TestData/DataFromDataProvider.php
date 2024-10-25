<?php declare(strict_types=1);








namespace PHPUnit\Event\TestData;

/**
@psalm-immutable
@no-named-arguments

*/
final class DataFromDataProvider extends TestData
{
private readonly int|string $dataSetName;
private readonly string $dataAsStringForResultOutput;

public static function from(int|string $dataSetName, string $data, string $dataAsStringForResultOutput): self
{
return new self($dataSetName, $data, $dataAsStringForResultOutput);
}

protected function __construct(int|string $dataSetName, string $data, string $dataAsStringForResultOutput)
{
$this->dataSetName = $dataSetName;
$this->dataAsStringForResultOutput = $dataAsStringForResultOutput;

parent::__construct($data);
}

public function dataSetName(): int|string
{
return $this->dataSetName;
}




public function dataAsStringForResultOutput(): string
{
return $this->dataAsStringForResultOutput;
}

/**
@psalm-assert-if-true
*/
public function isFromDataProvider(): bool
{
return true;
}
}
