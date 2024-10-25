<?php declare(strict_types=1);








namespace PHPUnit\Framework\Attributes;

use Attribute;

/**
@psalm-immutable
@no-named-arguments

*/
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class TestWithJson
{
/**
@psalm-var
*/
private readonly string $json;

/**
@psalm-param
*/
public function __construct(string $json)
{
$this->json = $json;
}

/**
@psalm-return
*/
public function json(): string
{
return $this->json;
}
}
