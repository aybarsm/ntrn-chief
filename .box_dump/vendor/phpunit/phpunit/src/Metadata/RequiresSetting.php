<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class RequiresSetting extends Metadata
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
@psalm-param
*/
protected function __construct(int $level, string $setting, string $value)
{
parent::__construct($level);

$this->setting = $setting;
$this->value = $value;
}

/**
@psalm-assert-if-true
*/
public function isRequiresSetting(): bool
{
return true;
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
