<?php











declare(strict_types=1);

namespace Ramsey\Uuid\Rfc4122;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Validator\ValidatorInterface;

use function preg_match;
use function str_replace;

/**
@psalm-immutable


*/
final class Validator implements ValidatorInterface
{
private const VALID_PATTERN = '\A[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-'
. '[1-8][0-9A-Fa-f]{3}-[ABab89][0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}\z';

/**
@psalm-return
@psalm-suppress
@psalm-suppress
*/
public function getPattern(): string
{
return self::VALID_PATTERN;
}

public function validate(string $uuid): bool
{
$uuid = str_replace(['urn:', 'uuid:', 'URN:', 'UUID:', '{', '}'], '', $uuid);
$uuid = strtolower($uuid);

return $uuid === Uuid::NIL || $uuid === Uuid::MAX || preg_match('/' . self::VALID_PATTERN . '/Dms', $uuid);
}
}
