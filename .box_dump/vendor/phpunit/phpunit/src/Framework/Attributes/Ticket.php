<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Ticket
{
/**
@psalm-var
*/
private readonly string $text;

/**
@psalm-param
*/
public function __construct(string $text)
{
$this->text = $text;
}

/**
@psalm-return
*/
public function text(): string
{
return $this->text;
}
}
