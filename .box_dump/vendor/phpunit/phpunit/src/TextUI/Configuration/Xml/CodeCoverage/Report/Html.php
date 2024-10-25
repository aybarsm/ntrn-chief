<?php declare(strict_types=1);








namespace PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report;

use PHPUnit\TextUI\Configuration\Directory;
use PHPUnit\TextUI\Configuration\NoCustomCssFileException;

/**
@no-named-arguments
@psalm-immutable



*/
final class Html
{
private readonly Directory $target;
private readonly int $lowUpperBound;
private readonly int $highLowerBound;
private readonly string $colorSuccessLow;
private readonly string $colorSuccessMedium;
private readonly string $colorSuccessHigh;
private readonly string $colorWarning;
private readonly string $colorDanger;
private readonly ?string $customCssFile;

public function __construct(Directory $target, int $lowUpperBound, int $highLowerBound, string $colorSuccessLow, string $colorSuccessMedium, string $colorSuccessHigh, string $colorWarning, string $colorDanger, ?string $customCssFile)
{
$this->target = $target;
$this->lowUpperBound = $lowUpperBound;
$this->highLowerBound = $highLowerBound;
$this->colorSuccessLow = $colorSuccessLow;
$this->colorSuccessMedium = $colorSuccessMedium;
$this->colorSuccessHigh = $colorSuccessHigh;
$this->colorWarning = $colorWarning;
$this->colorDanger = $colorDanger;
$this->customCssFile = $customCssFile;
}

public function target(): Directory
{
return $this->target;
}

public function lowUpperBound(): int
{
return $this->lowUpperBound;
}

public function highLowerBound(): int
{
return $this->highLowerBound;
}

public function colorSuccessLow(): string
{
return $this->colorSuccessLow;
}

public function colorSuccessMedium(): string
{
return $this->colorSuccessMedium;
}

public function colorSuccessHigh(): string
{
return $this->colorSuccessHigh;
}

public function colorWarning(): string
{
return $this->colorWarning;
}

public function colorDanger(): string
{
return $this->colorDanger;
}

/**
@psalm-assert-if-true
*/
public function hasCustomCssFile(): bool
{
return $this->customCssFile !== null;
}




public function customCssFile(): string
{
if (!$this->hasCustomCssFile()) {
throw new NoCustomCssFileException;
}

return $this->customCssFile;
}
}
