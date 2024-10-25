<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class RequiresOperatingSystem
{
/**
@psalm-var
*/
private readonly string $regularExpression;

/**
@psalm-param
*/
public function __construct(string $regularExpression)
{
$this->regularExpression = $regularExpression;
}

/**
@psalm-return
*/
public function regularExpression(): string
{
return $this->regularExpression;
}
}
