<?php declare(strict_types=1);








namespace PHPUnit\Framework;

/**
@no-named-arguments


*/
interface Reorderable
{
public function sortId(): string;

/**
@psalm-return
*/
public function provides(): array;

/**
@psalm-return
*/
public function requires(): array;
}
