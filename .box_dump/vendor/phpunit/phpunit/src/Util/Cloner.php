<?php declare(strict_types=1);








namespace PHPUnit\Util;

use Throwable;

/**
@no-named-arguments


*/
final class Cloner
{
/**
@psalm-template
@psalm-param
@psalm-return


*/
public static function clone(object $original): object
{
try {
return clone $original;
} catch (Throwable) {
return $original;
}
}
}
