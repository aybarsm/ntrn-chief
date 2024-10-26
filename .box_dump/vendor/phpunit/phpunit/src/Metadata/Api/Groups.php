<?php declare(strict_types=1);








namespace PHPUnit\Metadata\Api;

use function array_flip;
use function array_key_exists;
use function array_unique;
use function assert;
use function strtolower;
use function trim;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Metadata\Covers;
use PHPUnit\Metadata\CoversClass;
use PHPUnit\Metadata\CoversFunction;
use PHPUnit\Metadata\Group;
use PHPUnit\Metadata\Parser\Registry;
use PHPUnit\Metadata\Uses;
use PHPUnit\Metadata\UsesClass;
use PHPUnit\Metadata\UsesFunction;

/**
@no-named-arguments


*/
final class Groups
{



private static array $groupCache = [];

/**
@psalm-param
@psalm-param
@psalm-return

*/
public function groups(string $className, string $methodName, bool $includeVirtual = true): array
{
$key = $className . '::' . $methodName . '::' . $includeVirtual;

if (array_key_exists($key, self::$groupCache)) {
return self::$groupCache[$key];
}

$groups = [];

foreach (Registry::parser()->forClassAndMethod($className, $methodName)->isGroup() as $group) {
assert($group instanceof Group);

$groups[] = $group->groupName();
}

if ($groups === []) {
$groups[] = 'default';
}

if (!$includeVirtual) {
return self::$groupCache[$key] = array_unique($groups);
}

foreach (Registry::parser()->forClassAndMethod($className, $methodName) as $metadata) {
if ($metadata->isCoversClass() || $metadata->isCoversFunction()) {
assert($metadata instanceof CoversClass || $metadata instanceof CoversFunction);

$groups[] = '__phpunit_covers_' . $this->canonicalizeName($metadata->asStringForCodeUnitMapper());

continue;
}

if ($metadata->isCovers()) {
assert($metadata instanceof Covers);

$groups[] = '__phpunit_covers_' . $this->canonicalizeName($metadata->target());

continue;
}

if ($metadata->isUsesClass() || $metadata->isUsesFunction()) {
assert($metadata instanceof UsesClass || $metadata instanceof UsesFunction);

$groups[] = '__phpunit_uses_' . $this->canonicalizeName($metadata->asStringForCodeUnitMapper());

continue;
}

if ($metadata->isUses()) {
assert($metadata instanceof Uses);

$groups[] = '__phpunit_uses_' . $this->canonicalizeName($metadata->target());
}
}

return self::$groupCache[$key] = array_unique($groups);
}

/**
@psalm-param
@psalm-param
*/
public function size(string $className, string $methodName): TestSize
{
$groups = array_flip($this->groups($className, $methodName));

if (isset($groups['large'])) {
return TestSize::large();
}

if (isset($groups['medium'])) {
return TestSize::medium();
}

if (isset($groups['small'])) {
return TestSize::small();
}

return TestSize::unknown();
}

private function canonicalizeName(string $name): string
{
return strtolower(trim($name, '\\'));
}
}
