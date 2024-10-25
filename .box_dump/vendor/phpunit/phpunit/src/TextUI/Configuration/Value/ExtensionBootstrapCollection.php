<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

use IteratorAggregate;

/**
@template-implements
@no-named-arguments
@psalm-immutable


*/
final class ExtensionBootstrapCollection implements IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $extensionBootstraps;

/**
@psalm-param
*/
public static function fromArray(array $extensionBootstraps): self
{
return new self(...$extensionBootstraps);
}

private function __construct(ExtensionBootstrap ...$extensionBootstraps)
{
$this->extensionBootstraps = $extensionBootstraps;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->extensionBootstraps;
}

public function getIterator(): ExtensionBootstrapCollectionIterator
{
return new ExtensionBootstrapCollectionIterator($this);
}
}
