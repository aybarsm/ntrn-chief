<?php declare(strict_types=1);








namespace PHPUnit\Util;

use function in_array;

/**
@no-named-arguments
@psalm-immutable

*/
final class VersionComparisonOperator
{
/**
@psalm-var
*/
private readonly string $operator;

/**
@psalm-param


*/
public function __construct(string $operator)
{
$this->ensureOperatorIsValid($operator);

$this->operator = $operator;
}

/**
@psalm-return
*/
public function asString(): string
{
return $this->operator;
}

/**
@psalm-param


*/
private function ensureOperatorIsValid(string $operator): void
{
if (!in_array($operator, ['<', 'lt', '<=', 'le', '>', 'gt', '>=', 'ge', '==', '=', 'eq', '!=', '<>', 'ne'], true)) {
throw new InvalidVersionOperatorException($operator);
}
}
}
