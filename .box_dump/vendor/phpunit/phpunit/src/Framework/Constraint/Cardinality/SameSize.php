<?php declare(strict_types=1);








namespace PHPUnit\Framework\Constraint;

use Countable;
use PHPUnit\Framework\Exception;

/**
@no-named-arguments
*/
final class SameSize extends Count
{
/**
@psalm-param


*/
public function __construct($expected)
{
parent::__construct((int) $this->getCountOf($expected));
}
}
