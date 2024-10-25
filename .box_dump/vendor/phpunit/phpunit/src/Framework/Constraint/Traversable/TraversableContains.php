<?php declare(strict_types=1);








namespace PHPUnit\Framework\Constraint;

use function is_array;
use function sprintf;
use PHPUnit\Util\Exporter;

/**
@no-named-arguments
*/
abstract class TraversableContains extends Constraint
{
private readonly mixed $value;

public function __construct(mixed $value)
{
$this->value = $value;
}




public function toString(bool $exportObjects = false): string
{
return 'contains ' . Exporter::export($this->value, $exportObjects);
}







protected function failureDescription(mixed $other): string
{
return sprintf(
'%s %s',
is_array($other) ? 'an array' : 'a traversable',
$this->toString(true),
);
}

protected function value(): mixed
{
return $this->value;
}
}
