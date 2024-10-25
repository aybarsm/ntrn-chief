<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

/**
@psalm-immutable
@no-named-arguments

*/
final class TestDox extends Metadata
{
/**
@psalm-var
*/
private readonly string $text;

/**
@psalm-param
@psalm-param
*/
protected function __construct(int $level, string $text)
{
parent::__construct($level);

$this->text = $text;
}

/**
@psalm-assert-if-true
*/
public function isTestDox(): bool
{
return true;
}

/**
@psalm-return
*/
public function text(): string
{
return $this->text;
}
}
