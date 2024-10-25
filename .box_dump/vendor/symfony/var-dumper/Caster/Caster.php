<?php










namespace Symfony\Component\VarDumper\Caster;

use Symfony\Component\VarDumper\Cloner\Stub;








class Caster
{
public const EXCLUDE_VERBOSE = 1;
public const EXCLUDE_VIRTUAL = 2;
public const EXCLUDE_DYNAMIC = 4;
public const EXCLUDE_PUBLIC = 8;
public const EXCLUDE_PROTECTED = 16;
public const EXCLUDE_PRIVATE = 32;
public const EXCLUDE_NULL = 64;
public const EXCLUDE_EMPTY = 128;
public const EXCLUDE_NOT_IMPORTANT = 256;
public const EXCLUDE_STRICT = 512;
public const EXCLUDE_UNINITIALIZED = 1024;

public const PREFIX_VIRTUAL = "\0~\0";
public const PREFIX_DYNAMIC = "\0+\0";
public const PREFIX_PROTECTED = "\0*\0";

public const PATTERN_PRIVATE = "\0%s\0%s";

private static array $classProperties = [];






public static function castObject(object $obj, string $class, bool $hasDebugInfo = false, ?string $debugClass = null): array
{
if ($hasDebugInfo) {
try {
$debugInfo = $obj->__debugInfo();
} catch (\Throwable) {

$hasDebugInfo = false;
}
}

$a = $obj instanceof \Closure ? [] : (array) $obj;

if ($obj instanceof \__PHP_Incomplete_Class) {
return $a;
}

$classProperties = self::$classProperties[$class] ??= self::getClassProperties(new \ReflectionClass($class));
$a = array_replace($classProperties, $a);

if ($a) {
$debugClass ??= get_debug_type($obj);

$i = 0;
$prefixedKeys = [];
foreach ($a as $k => $v) {
if ("\0" !== ($k[0] ?? '')) {
if (!isset($classProperties[$k])) {
$prefixedKeys[$i] = self::PREFIX_DYNAMIC.$k;
}
} elseif ($debugClass !== $class && 1 === strpos($k, $class)) {
$prefixedKeys[$i] = "\0".$debugClass.strrchr($k, "\0");
}
++$i;
}
if ($prefixedKeys) {
$keys = array_keys($a);
foreach ($prefixedKeys as $i => $k) {
$keys[$i] = $k;
}
$a = array_combine($keys, $a);
}
}

if ($hasDebugInfo && \is_array($debugInfo)) {
foreach ($debugInfo as $k => $v) {
if (!isset($k[0]) || "\0" !== $k[0]) {
if (\array_key_exists(self::PREFIX_DYNAMIC.$k, $a)) {
continue;
}
$k = self::PREFIX_VIRTUAL.$k;
}

unset($a[$k]);
$a[$k] = $v;
}
}

return $a;
}












public static function filter(array $a, int $filter, array $listedProperties = [], ?int &$count = 0): array
{
$count = 0;

foreach ($a as $k => $v) {
$type = self::EXCLUDE_STRICT & $filter;

if (null === $v) {
$type |= self::EXCLUDE_NULL & $filter;
$type |= self::EXCLUDE_EMPTY & $filter;
} elseif (false === $v || '' === $v || '0' === $v || 0 === $v || 0.0 === $v || [] === $v) {
$type |= self::EXCLUDE_EMPTY & $filter;
} elseif ($v instanceof UninitializedStub) {
$type |= self::EXCLUDE_UNINITIALIZED & $filter;
}
if ((self::EXCLUDE_NOT_IMPORTANT & $filter) && !\in_array($k, $listedProperties, true)) {
$type |= self::EXCLUDE_NOT_IMPORTANT;
}
if ((self::EXCLUDE_VERBOSE & $filter) && \in_array($k, $listedProperties, true)) {
$type |= self::EXCLUDE_VERBOSE;
}

if (!isset($k[1]) || "\0" !== $k[0]) {
$type |= self::EXCLUDE_PUBLIC & $filter;
} elseif ('~' === $k[1]) {
$type |= self::EXCLUDE_VIRTUAL & $filter;
} elseif ('+' === $k[1]) {
$type |= self::EXCLUDE_DYNAMIC & $filter;
} elseif ('*' === $k[1]) {
$type |= self::EXCLUDE_PROTECTED & $filter;
} else {
$type |= self::EXCLUDE_PRIVATE & $filter;
}

if ((self::EXCLUDE_STRICT & $filter) ? $type === $filter : $type) {
unset($a[$k]);
++$count;
}
}

return $a;
}

public static function castPhpIncompleteClass(\__PHP_Incomplete_Class $c, array $a, Stub $stub, bool $isNested): array
{
if (isset($a['__PHP_Incomplete_Class_Name'])) {
$stub->class .= '('.$a['__PHP_Incomplete_Class_Name'].')';
unset($a['__PHP_Incomplete_Class_Name']);
}

return $a;
}

private static function getClassProperties(\ReflectionClass $class): array
{
$classProperties = [];
$className = $class->name;

if ($parent = $class->getParentClass()) {
$classProperties += self::$classProperties[$parent->name] ??= self::getClassProperties($parent);
}

foreach ($class->getProperties() as $p) {
if ($p->isStatic()) {
continue;
}

$classProperties[match (true) {
$p->isPublic() => $p->name,
$p->isProtected() => self::PREFIX_PROTECTED.$p->name,
default => "\0".$className."\0".$p->name,
}] = new UninitializedStub($p);
}

return $classProperties;
}
}