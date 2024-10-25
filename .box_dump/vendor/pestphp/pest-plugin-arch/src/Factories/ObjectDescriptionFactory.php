<?php

declare(strict_types=1);

namespace Pest\Arch\Factories;

use Pest\Arch\Objects\ObjectDescription;
use Pest\Arch\Objects\VendorObjectDescription;
use Pest\Arch\Support\PhpCoreExpressions;
use PHPUnit\Architecture\Asserts\Dependencies\Elements\ObjectUses;
use PHPUnit\Architecture\Services\ServiceContainer;
use ReflectionClass;
use ReflectionFunction;




final class ObjectDescriptionFactory
{



private static bool $serviceContainerInitialized = false;




public static function make(string $filename, bool $onlyUserDefinedUses = true): ?\PHPUnit\Architecture\Elements\ObjectDescription
{
self::ensureServiceContainerIsInitialized();

$isFromVendor = str_contains((string) realpath($filename), DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR);

$originalErrorReportingLevel = error_reporting();
error_reporting($originalErrorReportingLevel & ~E_USER_DEPRECATED);

try {
$object = $isFromVendor
? VendorObjectDescription::make($filename)
: ObjectDescription::make($filename);

} finally {
error_reporting($originalErrorReportingLevel);
}

if ($object === null) {
return null;
}

if (! $isFromVendor) {
$object->uses = new ObjectUses(array_values(
array_filter(
iterator_to_array($object->uses->getIterator()),
static fn (string $use): bool => (! $onlyUserDefinedUses || self::isUserDefined($use)) && ! self::isSameLayer($object, $use),
)
));
}

return $object;
}




private static function ensureServiceContainerIsInitialized(): void
{
if (! self::$serviceContainerInitialized) {
ServiceContainer::init();

self::$serviceContainerInitialized = true;
}
}




private static function isUserDefined(string $use): bool
{
if (PhpCoreExpressions::getClass($use) !== null) {
return false;
}

return match (true) {
enum_exists($use) => (new \ReflectionEnum($use))->isUserDefined(),
function_exists($use) => (new ReflectionFunction($use))->isUserDefined(),
class_exists($use) => (new ReflectionClass($use))->isUserDefined(),
interface_exists($use) => (new ReflectionClass($use))->isUserDefined(),


default => true,
};
}




private static function isSameLayer(\PHPUnit\Architecture\Elements\ObjectDescription $object, string $use): bool
{
return $use === 'self'
|| $use === 'static'
|| $use === 'parent'
|| $object->reflectionClass->getNamespaceName() === $use;
}
}