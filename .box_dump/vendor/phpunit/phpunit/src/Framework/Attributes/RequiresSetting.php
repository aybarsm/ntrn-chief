<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class RequiresSetting
{
/**
@psalm-var
*/
private readonly string $setting;

/**
@psalm-var
*/
private readonly string $value;

/**
@psalm-param
@psalm-param
*/
public function __construct(string $setting, string $value)
{
$this->setting = $setting;
$this->value = $value;
}

/**
@psalm-return
*/
public function setting(): string
{
return $this->setting;
}

/**
@psalm-return
*/
public function value(): string
{
return $this->value;
}
}
