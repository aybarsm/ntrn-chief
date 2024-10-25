<?php declare(strict_types=1);








namespace PHPUnit\Metadata\Parser;

use PHPUnit\Metadata\MetadataCollection;

/**
@no-named-arguments


*/
interface Parser
{
/**
@psalm-param
*/
public function forClass(string $className): MetadataCollection;

/**
@psalm-param
@psalm-param
*/
public function forMethod(string $className, string $methodName): MetadataCollection;

/**
@psalm-param
@psalm-param
*/
public function forClassAndMethod(string $className, string $methodName): MetadataCollection;
}
