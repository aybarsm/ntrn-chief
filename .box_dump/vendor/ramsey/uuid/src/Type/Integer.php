<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Type;

use Ramsey\Uuid\Exception\InvalidArgumentException;
use ValueError;

use function assert;
use function is_numeric;
use function preg_match;
use function sprintf;
use function substr;

/**
@psalm-immutable









*/
final class Integer implements NumberInterface
{
/**
@psalm-var
*/
private string $value;

private bool $isNegative = false;

public function __construct(float | int | string | self $value)
{
$this->value = $value instanceof self ? (string) $value : $this->prepareValue($value);
}

public function isNegative(): bool
{
return $this->isNegative;
}

/**
@psalm-return
*/
public function toString(): string
{
return $this->value;
}

/**
@psalm-return
*/
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




public function __unserialize(array $data): void
{

if (!isset($data['string'])) {
throw new ValueError(sprintf('%s(): Argument #1 ($data) is invalid', __METHOD__));
}


$this->unserialize($data['string']);
}




private function prepareValue(float | int | string $value): string
{
$value = (string) $value;
$sign = '+';


if (str_starts_with($value, '-') || str_starts_with($value, '+')) {
$sign = substr($value, 0, 1);
$value = substr($value, 1);
}

if (!preg_match('/^\d+$/', $value)) {
throw new InvalidArgumentException(
'Value must be a signed integer or a string containing only '
. 'digits 0-9 and, optionally, a sign (+ or -)'
);
}


$value = ltrim($value, '0');


if ($value === '') {
$value = '0';
}


if ($sign === '-' && $value !== '0') {
$value = $sign . $value;

/**
@psalm-suppress */
$this->isNegative = true;
}

assert(is_numeric($value));

return $value;
}
}
