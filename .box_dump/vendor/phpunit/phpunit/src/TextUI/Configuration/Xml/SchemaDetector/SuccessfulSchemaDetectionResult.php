<?php declare(strict_types=1);








namespace PHPUnit\TextUI\XmlConfiguration;

/**
@no-named-arguments
@psalm-immutable



*/
final class SuccessfulSchemaDetectionResult extends SchemaDetectionResult
{
/**
@psalm-var
*/
private readonly string $version;

/**
@psalm-param
*/
public function __construct(string $version)
{
$this->version = $version;
}

/**
@psalm-assert-if-true
*/
public function detected(): bool
{
return true;
}

/**
@psalm-return
*/
public function version(): string
{
return $this->version;
}
}
