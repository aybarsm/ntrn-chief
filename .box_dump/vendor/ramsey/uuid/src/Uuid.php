<?php











declare(strict_types=1);

namespace Ramsey\Uuid;

use BadMethodCallException;
use DateTimeInterface;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
use Ramsey\Uuid\Fields\FieldsInterface;
use Ramsey\Uuid\Lazy\LazyUuidFromString;
use Ramsey\Uuid\Rfc4122\FieldsInterface as Rfc4122FieldsInterface;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use ValueError;

use function assert;
use function bin2hex;
use function method_exists;
use function preg_match;
use function sprintf;
use function str_replace;
use function strcmp;
use function strlen;
use function strtolower;
use function substr;

/**
@psalm-immutable


*/
class Uuid implements UuidInterface
{
use DeprecatedUuidMethodsTrait;







public const NAMESPACE_DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';






public const NAMESPACE_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';






public const NAMESPACE_OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';







public const NAMESPACE_X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';







public const NIL = '00000000-0000-0000-0000-000000000000';







public const MAX = 'ffffffff-ffff-ffff-ffff-ffffffffffff';






public const RESERVED_NCS = 0;






public const RFC_4122 = 2;






public const RESERVED_MICROSOFT = 6;






public const RESERVED_FUTURE = 7;




public const VALID_PATTERN = '^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$';






public const UUID_TYPE_TIME = 1;






public const UUID_TYPE_DCE_SECURITY = 2;




public const UUID_TYPE_IDENTIFIER = 2;






public const UUID_TYPE_HASH_MD5 = 3;






public const UUID_TYPE_RANDOM = 4;






public const UUID_TYPE_HASH_SHA1 = 5;




public const UUID_TYPE_PEABODY = 6;






public const UUID_TYPE_REORDERED_TIME = 6;






public const UUID_TYPE_UNIX_TIME = 7;




public const UUID_TYPE_CUSTOM = 8;






public const DCE_DOMAIN_PERSON = 0;






public const DCE_DOMAIN_GROUP = 1;






public const DCE_DOMAIN_ORG = 2;






public const DCE_DOMAIN_NAMES = [
self::DCE_DOMAIN_PERSON => 'person',
self::DCE_DOMAIN_GROUP => 'group',
self::DCE_DOMAIN_ORG => 'org',
];

private static ?UuidFactoryInterface $factory = null;






private static bool $factoryReplaced = false;

protected CodecInterface $codec;
protected NumberConverterInterface $numberConverter;
protected Rfc4122FieldsInterface $fields;
protected TimeConverterInterface $timeConverter;

























public function __construct(
Rfc4122FieldsInterface $fields,
NumberConverterInterface $numberConverter,
CodecInterface $codec,
TimeConverterInterface $timeConverter
) {
$this->fields = $fields;
$this->codec = $codec;
$this->numberConverter = $numberConverter;
$this->timeConverter = $timeConverter;
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
return $this->codec->encode($this);
}




public function __serialize(): array
{
return ['bytes' => $this->serialize()];
}







public function unserialize(string $data): void
{
if (strlen($data) === 16) {

$uuid = self::getFactory()->fromBytes($data);
} else {

$uuid = self::getFactory()->fromString($data);
}

$this->codec = $uuid->codec;
$this->numberConverter = $uuid->numberConverter;
$this->fields = $uuid->fields;
$this->timeConverter = $uuid->timeConverter;
}




public function __unserialize(array $data): void
{

if (!isset($data['bytes'])) {
throw new ValueError(sprintf('%s(): Argument #1 ($data) is invalid', __METHOD__));
}


$this->unserialize($data['bytes']);
}

public function compareTo(UuidInterface $other): int
{
$compare = strcmp($this->toString(), $other->toString());

if ($compare < 0) {
return -1;
}

if ($compare > 0) {
return 1;
}

return 0;
}

public function equals(?object $other): bool
{
if (!$other instanceof UuidInterface) {
return false;
}

return $this->compareTo($other) === 0;
}

/**
@psalm-return
*/
public function getBytes(): string
{
return $this->codec->encodeBinary($this);
}

public function getFields(): FieldsInterface
{
return $this->fields;
}

public function getHex(): Hexadecimal
{
return new Hexadecimal(str_replace('-', '', $this->toString()));
}

public function getInteger(): IntegerObject
{
return new IntegerObject($this->numberConverter->fromHex($this->getHex()->toString()));
}

public function getUrn(): string
{
return 'urn:uuid:' . $this->toString();
}

/**
@psalm-return
*/
public function toString(): string
{
return $this->codec->encode($this);
}




public static function getFactory(): UuidFactoryInterface
{
if (self::$factory === null) {
self::$factory = new UuidFactory();
}

return self::$factory;
}







public static function setFactory(UuidFactoryInterface $factory): void
{



self::$factoryReplaced = ($factory != new UuidFactory());

self::$factory = $factory;
}

/**
@psalm-pure
@psalm-suppress












*/
public static function fromBytes(string $bytes): UuidInterface
{
if (!self::$factoryReplaced && strlen($bytes) === 16) {
$base16Uuid = bin2hex($bytes);


return self::fromString(
substr($base16Uuid, 0, 8)
. '-'
. substr($base16Uuid, 8, 4)
. '-'
. substr($base16Uuid, 12, 4)
. '-'
. substr($base16Uuid, 16, 4)
. '-'
. substr($base16Uuid, 20, 12)
);
}

return self::getFactory()->fromBytes($bytes);
}

/**
@psalm-pure
@psalm-suppress












*/
public static function fromString(string $uuid): UuidInterface
{
$uuid = strtolower($uuid);
if (!self::$factoryReplaced && preg_match(LazyUuidFromString::VALID_REGEX, $uuid) === 1) {
assert($uuid !== '');

return new LazyUuidFromString($uuid);
}

return self::getFactory()->fromString($uuid);
}














public static function fromDateTime(
DateTimeInterface $dateTime,
?Hexadecimal $node = null,
?int $clockSeq = null
): UuidInterface {
return self::getFactory()->fromDateTime($dateTime, $node, $clockSeq);
}

/**
@psalm-pure
@psalm-suppress








*/
public static function fromHexadecimal(Hexadecimal $hex): UuidInterface
{
$factory = self::getFactory();

if (method_exists($factory, 'fromHexadecimal')) {
/**
@phpstan-ignore-next-line
@psalm-suppress
*/
return self::getFactory()->fromHexadecimal($hex);
}

throw new BadMethodCallException('The method fromHexadecimal() does not exist on the provided factory');
}

/**
@psalm-pure








*/
public static function fromInteger(string $integer): UuidInterface
{
/**
@psalm-suppress */
return self::getFactory()->fromInteger($integer);
}

/**
@psalm-pure
@psalm-assert-if-true








*/
public static function isValid(string $uuid): bool
{
/**
@psalm-suppress */
return self::getFactory()->getValidator()->validate($uuid);
}















public static function uuid1($node = null, ?int $clockSeq = null): UuidInterface
{
return self::getFactory()->uuid1($node, $clockSeq);
}





















public static function uuid2(
int $localDomain,
?IntegerObject $localIdentifier = null,
?Hexadecimal $node = null,
?int $clockSeq = null
): UuidInterface {
return self::getFactory()->uuid2($localDomain, $localIdentifier, $node, $clockSeq);
}

/**
@psalm-suppress
@psalm-pure














*/
public static function uuid3($ns, string $name): UuidInterface
{
return self::getFactory()->uuid3($ns, $name);
}







public static function uuid4(): UuidInterface
{
return self::getFactory()->uuid4();
}

/**
@psalm-pure
@psalm-suppress














*/
public static function uuid5($ns, string $name): UuidInterface
{
return self::getFactory()->uuid5($ns, $name);
}














public static function uuid6(
?Hexadecimal $node = null,
?int $clockSeq = null
): UuidInterface {
return self::getFactory()->uuid6($node, $clockSeq);
}











public static function uuid7(?DateTimeInterface $dateTime = null): UuidInterface
{
$factory = self::getFactory();

if (method_exists($factory, 'uuid7')) {

return $factory->uuid7($dateTime);
}

throw new UnsupportedOperationException(
'The provided factory does not support the uuid7() method',
);
}

















public static function uuid8(string $bytes): UuidInterface
{
$factory = self::getFactory();

if (method_exists($factory, 'uuid8')) {

return $factory->uuid8($bytes);
}

throw new UnsupportedOperationException(
'The provided factory does not support the uuid8() method',
);
}
}
