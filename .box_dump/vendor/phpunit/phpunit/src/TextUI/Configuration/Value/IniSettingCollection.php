<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

use function count;
use Countable;
use IteratorAggregate;

/**
@no-named-arguments
@psalm-immutable
@template-implements


*/
final class IniSettingCollection implements Countable, IteratorAggregate
{
/**
@psalm-var
*/
private readonly array $iniSettings;

/**
@psalm-param
*/
public static function fromArray(array $iniSettings): self
{
return new self(...$iniSettings);
}

private function __construct(IniSetting ...$iniSettings)
{
$this->iniSettings = $iniSettings;
}

/**
@psalm-return
*/
public function asArray(): array
{
return $this->iniSettings;
}

public function count(): int
{
return count($this->iniSettings);
}

public function getIterator(): IniSettingCollectionIterator
{
return new IniSettingCollectionIterator($this);
}
}
