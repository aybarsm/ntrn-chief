<?php declare(strict_types=1);








namespace PHPUnit\Framework;

use function sprintf;

/**
@no-named-arguments


*/
final class ComparisonMethodDoesNotExistException extends Exception
{
public function __construct(string $className, string $methodName)
{
parent::__construct(
sprintf(
'Comparison method %s::%s() does not exist.',
$className,
$methodName,
),
);
}
}