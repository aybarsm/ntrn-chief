<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Type;

use Ramsey\Uuid\Exception\InvalidArgumentException;
use ValueError;

use function is_numeric;
use function sprintf;
use function str_starts_with;

/**
@psalm-immutable









*/
final class Decimal implements NumberInterface
{
private string $value;
private bool $isNegative = false;

public function __construct(float | int | string | self $value)
{
$value = (string) $value;

if (!is_numeric($value)) {
throw new InvalidArgumentException(
'Value must be a signed decimal or a string containing only '
. 'digits 0-9 and, optionally, a decimal point or sign (+ or -)'
);
}


if (str_starts_with($value, '+')) {
$value = substr($value, 1);
}


if (abs((float) $value) === 0.0) {
$value = '0';
}

if (str_starts_with($value, '-')) {
$this->isNegative = true;
}

$this->value = $value;
}

public function isNegative(): bool
{
return $this->isNegative;
}

public function toString(): string
{
return $this->value;
}

public function __toString(): string
{
return $this->toString();
}

public function jsonSerialize(): string
{
return $this->toString();
}

public function serialize(): string
{
return $this->toString();
}




public function __serialize(): array
{
return ['string' => $this->toString()];
}

/**
@psalm-suppress




*/
public function unserialize(string $data): void
{
$this->__construct($data);
}

/**
@psalm-suppress


*/
public function __unserialize(array $data): void
{

if (!isset($data['string'])) {
throw new ValueError(sprintf('%s(): Argument #1 ($data) is invalid', __METHOD__));
}


$this->unserialize($data['string']);
}
}
