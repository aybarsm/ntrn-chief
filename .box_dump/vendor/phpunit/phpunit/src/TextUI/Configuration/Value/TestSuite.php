<?php declare(strict_types=1);








namespace PHPUnit\TextUI\Configuration;

/**
@no-named-arguments
@psalm-immutable

*/
final class TestSuite
{
/**
@psalm-var
*/
private readonly string $name;
private readonly TestDirectoryCollection $directories;
private readonly TestFileCollection $files;
private readonly FileCollection $exclude;

/**
@psalm-param
*/
public function __construct(string $name, TestDirectoryCollection $directories, TestFileCollection $files, FileCollection $exclude)
{
$this->name = $name;
$this->directories = $directories;
$this->files = $files;
$this->exclude = $exclude;
}

/**
@psalm-return
*/
public function name(): string
{
return $this->name;
}

public function directories(): TestDirectoryCollection
{
return $this->directories;
}

public function files(): TestFileCollection
{
return $this->files;
}

public function exclude(): FileCollection
{
return $this->exclude;
}
}
