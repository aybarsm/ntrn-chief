<?php

namespace Illuminate\Database\Eloquent\Concerns;

use BackedEnum;
use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException as BrickMathException;
use Brick\Math\RoundingMode;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\Casts\AsEncryptedCollection;
use Illuminate\Database\Eloquent\Casts\AsEnumArrayObject;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Exceptions\MathException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;
use ValueError;

use function Illuminate\Support\enum_value;

trait HasAttributes
{





protected $attributes = [];






protected $original = [];






protected $changes = [];






protected $casts = [];






protected $classCastCache = [];






protected $attributeCastCache = [];






protected static $primitiveCastTypes = [
'array',
'bool',
'boolean',
'collection',
'custom_datetime',
'date',
'datetime',
'decimal',
'double',
'encrypted',
'encrypted:array',
'encrypted:collection',
'encrypted:json',
'encrypted:object',
'float',
'hashed',
'immutable_date',
'immutable_datetime',
'immutable_custom_datetime',
'int',
'integer',
'json',
'object',
'real',
'string',
'timestamp',
];






protected $dateFormat;






protected $appends = [];






public static $snakeAttributes = true;






protected static $mutatorCache = [];






protected static $attributeMutatorCache = [];






protected static $getAttributeMutatorCache = [];






protected static $setAttributeMutatorCache = [];






protected static $castTypeCache = [];






public static $encrypter;






protected function initializeHasAttributes()
{
$this->casts = $this->ensureCastsAreStringValues(
array_merge($this->casts, $this->casts()),
);
}






public function attributesToArray()
{



$attributes = $this->addDateAttributesToArray(
$attributes = $this->getArrayableAttributes()
);

$attributes = $this->addMutatedAttributesToArray(
$attributes, $mutatedAttributes = $this->getMutatedAttributes()
);




$attributes = $this->addCastAttributesToArray(
$attributes, $mutatedAttributes
);




foreach ($this->getArrayableAppends() as $key) {
$attributes[$key] = $this->mutateAttributeForArray($key, null);
}

return $attributes;
}







protected function addDateAttributesToArray(array $attributes)
{
foreach ($this->getDates() as $key) {
if (! isset($attributes[$key])) {
continue;
}

$attributes[$key] = $this->serializeDate(
$this->asDateTime($attributes[$key])
);
}

return $attributes;
}








protected function addMutatedAttributesToArray(array $attributes, array $mutatedAttributes)
{
foreach ($mutatedAttributes as $key) {



if (! array_key_exists($key, $attributes)) {
continue;
}




$attributes[$key] = $this->mutateAttributeForArray(
$key, $attributes[$key]
);
}

return $attributes;
}








protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes)
{
foreach ($this->getCasts() as $key => $value) {
if (! array_key_exists($key, $attributes) ||
in_array($key, $mutatedAttributes)) {
continue;
}




$attributes[$key] = $this->castAttribute(
$key, $attributes[$key]
);




if (isset($attributes[$key]) && in_array($value, ['date', 'datetime', 'immutable_date', 'immutable_datetime'])) {
$attributes[$key] = $this->serializeDate($attributes[$key]);
}

if (isset($attributes[$key]) && ($this->isCustomDateTimeCast($value) ||
$this->isImmutableCustomDateTimeCast($value))) {
$attributes[$key] = $attributes[$key]->format(explode(':', $value, 2)[1]);
}

if ($attributes[$key] instanceof DateTimeInterface &&
$this->isClassCastable($key)) {
$attributes[$key] = $this->serializeDate($attributes[$key]);
}

if (isset($attributes[$key]) && $this->isClassSerializable($key)) {
$attributes[$key] = $this->serializeClassCastableAttribute($key, $attributes[$key]);
}

if ($this->isEnumCastable($key) && (! ($attributes[$key] ?? null) instanceof Arrayable)) {
$attributes[$key] = isset($attributes[$key]) ? $this->getStorableEnumValue($this->getCasts()[$key], $attributes[$key]) : null;
}

if ($attributes[$key] instanceof Arrayable) {
$attributes[$key] = $attributes[$key]->toArray();
}
}

return $attributes;
}






protected function getArrayableAttributes()
{
return $this->getArrayableItems($this->getAttributes());
}






protected function getArrayableAppends()
{
if (! count($this->appends)) {
return [];
}

return $this->getArrayableItems(
array_combine($this->appends, $this->appends)
);
}






public function relationsToArray()
{
$attributes = [];

foreach ($this->getArrayableRelations() as $key => $value) {



if ($value instanceof Arrayable) {
$relation = $value->toArray();
}




elseif (is_null($value)) {
$relation = $value;
}




if (static::$snakeAttributes) {
$key = Str::snake($key);
}




if (array_key_exists('relation', get_defined_vars())) { 
$attributes[$key] = $relation ?? null;
}

unset($relation);
}

return $attributes;
}






protected function getArrayableRelations()
{
return $this->getArrayableItems($this->relations);
}







protected function getArrayableItems(array $values)
{
if (count($this->getVisible()) > 0) {
$values = array_intersect_key($values, array_flip($this->getVisible()));
}

if (count($this->getHidden()) > 0) {
$values = array_diff_key($values, array_flip($this->getHidden()));
}

return $values;
}







public function hasAttribute($key)
{
if (! $key) {
return false;
}

return array_key_exists($key, $this->attributes) ||
array_key_exists($key, $this->casts) ||
$this->hasGetMutator($key) ||
$this->hasAttributeMutator($key) ||
$this->isClassCastable($key);
}







public function getAttribute($key)
{
if (! $key) {
return;
}




if ($this->hasAttribute($key)) {
return $this->getAttributeValue($key);
}




if (method_exists(self::class, $key)) {
return $this->throwMissingAttributeExceptionIfApplicable($key);
}

return $this->isRelation($key) || $this->relationLoaded($key)
? $this->getRelationValue($key)
: $this->throwMissingAttributeExceptionIfApplicable($key);
}









protected function throwMissingAttributeExceptionIfApplicable($key)
{
if ($this->exists &&
! $this->wasRecentlyCreated &&
static::preventsAccessingMissingAttributes()) {
if (isset(static::$missingAttributeViolationCallback)) {
return call_user_func(static::$missingAttributeViolationCallback, $this, $key);
}

throw new MissingAttributeException($this, $key);
}

return null;
}







public function getAttributeValue($key)
{
return $this->transformModelValue($key, $this->getAttributeFromArray($key));
}







protected function getAttributeFromArray($key)
{
return $this->getAttributes()[$key] ?? null;
}







public function getRelationValue($key)
{



if ($this->relationLoaded($key)) {
return $this->relations[$key];
}

if (! $this->isRelation($key)) {
return;
}

if ($this->preventsLazyLoading) {
$this->handleLazyLoadingViolation($key);
}




return $this->getRelationshipFromMethod($key);
}







public function isRelation($key)
{
if ($this->hasAttributeMutator($key)) {
return false;
}

return method_exists($this, $key) ||
$this->relationResolver(static::class, $key);
}







protected function handleLazyLoadingViolation($key)
{
if (isset(static::$lazyLoadingViolationCallback)) {
return call_user_func(static::$lazyLoadingViolationCallback, $this, $key);
}

if (! $this->exists || $this->wasRecentlyCreated) {
return;
}

throw new LazyLoadingViolationException($this, $key);
}









protected function getRelationshipFromMethod($method)
{
$relation = $this->$method();

if (! $relation instanceof Relation) {
if (is_null($relation)) {
throw new LogicException(sprintf(
'%s::%s must return a relationship instance, but "null" was returned. Was the "return" keyword used?', static::class, $method
));
}

throw new LogicException(sprintf(
'%s::%s must return a relationship instance.', static::class, $method
));
}

return tap($relation->getResults(), function ($results) use ($method) {
$this->setRelation($method, $results);
});
}







public function hasGetMutator($key)
{
return method_exists($this, 'get'.Str::studly($key).'Attribute');
}







public function hasAttributeMutator($key)
{
if (isset(static::$attributeMutatorCache[get_class($this)][$key])) {
return static::$attributeMutatorCache[get_class($this)][$key];
}

if (! method_exists($this, $method = Str::camel($key))) {
return static::$attributeMutatorCache[get_class($this)][$key] = false;
}

$returnType = (new ReflectionMethod($this, $method))->getReturnType();

return static::$attributeMutatorCache[get_class($this)][$key] =
$returnType instanceof ReflectionNamedType &&
$returnType->getName() === Attribute::class;
}







public function hasAttributeGetMutator($key)
{
if (isset(static::$getAttributeMutatorCache[get_class($this)][$key])) {
return static::$getAttributeMutatorCache[get_class($this)][$key];
}

if (! $this->hasAttributeMutator($key)) {
return static::$getAttributeMutatorCache[get_class($this)][$key] = false;
}

return static::$getAttributeMutatorCache[get_class($this)][$key] = is_callable($this->{Str::camel($key)}()->get);
}








protected function mutateAttribute($key, $value)
{
return $this->{'get'.Str::studly($key).'Attribute'}($value);
}








protected function mutateAttributeMarkedAttribute($key, $value)
{
if (array_key_exists($key, $this->attributeCastCache)) {
return $this->attributeCastCache[$key];
}

$attribute = $this->{Str::camel($key)}();

$value = call_user_func($attribute->get ?: function ($value) {
return $value;
}, $value, $this->attributes);

if ($attribute->withCaching || (is_object($value) && $attribute->withObjectCaching)) {
$this->attributeCastCache[$key] = $value;
} else {
unset($this->attributeCastCache[$key]);
}

return $value;
}








protected function mutateAttributeForArray($key, $value)
{
if ($this->isClassCastable($key)) {
$value = $this->getClassCastableAttributeValue($key, $value);
} elseif (isset(static::$getAttributeMutatorCache[get_class($this)][$key]) &&
static::$getAttributeMutatorCache[get_class($this)][$key] === true) {
$value = $this->mutateAttributeMarkedAttribute($key, $value);

$value = $value instanceof DateTimeInterface
? $this->serializeDate($value)
: $value;
} else {
$value = $this->mutateAttribute($key, $value);
}

return $value instanceof Arrayable ? $value->toArray() : $value;
}







public function mergeCasts($casts)
{
$casts = $this->ensureCastsAreStringValues($casts);

$this->casts = array_merge($this->casts, $casts);

return $this;
}







protected function ensureCastsAreStringValues($casts)
{
foreach ($casts as $attribute => $cast) {
$casts[$attribute] = match (true) {
is_array($cast) => value(function () use ($cast) {
if (count($cast) === 1) {
return $cast[0];
}

[$cast, $arguments] = [array_shift($cast), $cast];

return $cast.':'.implode(',', $arguments);
}),
default => $cast,
};
}

return $casts;
}








protected function castAttribute($key, $value)
{
$castType = $this->getCastType($key);

if (is_null($value) && in_array($castType, static::$primitiveCastTypes)) {
return $value;
}




if ($this->isEncryptedCastable($key)) {
$value = $this->fromEncryptedString($value);

$castType = Str::after($castType, 'encrypted:');
}

switch ($castType) {
case 'int':
case 'integer':
return (int) $value;
case 'real':
case 'float':
case 'double':
return $this->fromFloat($value);
case 'decimal':
return $this->asDecimal($value, explode(':', $this->getCasts()[$key], 2)[1]);
case 'string':
return (string) $value;
case 'bool':
case 'boolean':
return (bool) $value;
case 'object':
return $this->fromJson($value, true);
case 'array':
case 'json':
return $this->fromJson($value);
case 'collection':
return new BaseCollection($this->fromJson($value));
case 'date':
return $this->asDate($value);
case 'datetime':
case 'custom_datetime':
return $this->asDateTime($value);
case 'immutable_date':
return $this->asDate($value)->toImmutable();
case 'immutable_custom_datetime':
case 'immutable_datetime':
return $this->asDateTime($value)->toImmutable();
case 'timestamp':
return $this->asTimestamp($value);
}

if ($this->isEnumCastable($key)) {
return $this->getEnumCastableAttributeValue($key, $value);
}

if ($this->isClassCastable($key)) {
return $this->getClassCastableAttributeValue($key, $value);
}

return $value;
}








protected function getClassCastableAttributeValue($key, $value)
{
$caster = $this->resolveCasterClass($key);

$objectCachingDisabled = $caster->withoutObjectCaching ?? false;

if (isset($this->classCastCache[$key]) && ! $objectCachingDisabled) {
return $this->classCastCache[$key];
} else {
$value = $caster instanceof CastsInboundAttributes
? $value
: $caster->get($this, $key, $value, $this->attributes);

if ($caster instanceof CastsInboundAttributes ||
! is_object($value) ||
$objectCachingDisabled) {
unset($this->classCastCache[$key]);
} else {
$this->classCastCache[$key] = $value;
}

return $value;
}
}








protected function getEnumCastableAttributeValue($key, $value)
{
if (is_null($value)) {
return;
}

$castType = $this->getCasts()[$key];

if ($value instanceof $castType) {
return $value;
}

return $this->getEnumCaseFromValue($castType, $value);
}







protected function getCastType($key)
{
$castType = $this->getCasts()[$key];

if (isset(static::$castTypeCache[$castType])) {
return static::$castTypeCache[$castType];
}

if ($this->isCustomDateTimeCast($castType)) {
$convertedCastType = 'custom_datetime';
} elseif ($this->isImmutableCustomDateTimeCast($castType)) {
$convertedCastType = 'immutable_custom_datetime';
} elseif ($this->isDecimalCast($castType)) {
$convertedCastType = 'decimal';
} elseif (class_exists($castType)) {
$convertedCastType = $castType;
} else {
$convertedCastType = trim(strtolower($castType));
}

return static::$castTypeCache[$castType] = $convertedCastType;
}









protected function deviateClassCastableAttribute($method, $key, $value)
{
return $this->resolveCasterClass($key)->{$method}(
$this, $key, $value, $this->attributes
);
}








protected function serializeClassCastableAttribute($key, $value)
{
return $this->resolveCasterClass($key)->serialize(
$this, $key, $value, $this->attributes
);
}







protected function isCustomDateTimeCast($cast)
{
return str_starts_with($cast, 'date:') ||
str_starts_with($cast, 'datetime:');
}







protected function isImmutableCustomDateTimeCast($cast)
{
return str_starts_with($cast, 'immutable_date:') ||
str_starts_with($cast, 'immutable_datetime:');
}







protected function isDecimalCast($cast)
{
return str_starts_with($cast, 'decimal:');
}








public function setAttribute($key, $value)
{



if ($this->hasSetMutator($key)) {
return $this->setMutatedAttributeValue($key, $value);
} elseif ($this->hasAttributeSetMutator($key)) {
return $this->setAttributeMarkedMutatedAttributeValue($key, $value);
}




elseif (! is_null($value) && $this->isDateAttribute($key)) {
$value = $this->fromDateTime($value);
}

if ($this->isEnumCastable($key)) {
$this->setEnumCastableAttribute($key, $value);

return $this;
}

if ($this->isClassCastable($key)) {
$this->setClassCastableAttribute($key, $value);

return $this;
}

if (! is_null($value) && $this->isJsonCastable($key)) {
$value = $this->castAttributeAsJson($key, $value);
}




if (str_contains($key, '->')) {
return $this->fillJsonAttribute($key, $value);
}

if (! is_null($value) && $this->isEncryptedCastable($key)) {
$value = $this->castAttributeAsEncryptedString($key, $value);
}

if (! is_null($value) && $this->hasCast($key, 'hashed')) {
$value = $this->castAttributeAsHashedString($key, $value);
}

$this->attributes[$key] = $value;

return $this;
}







public function hasSetMutator($key)
{
return method_exists($this, 'set'.Str::studly($key).'Attribute');
}







public function hasAttributeSetMutator($key)
{
$class = get_class($this);

if (isset(static::$setAttributeMutatorCache[$class][$key])) {
return static::$setAttributeMutatorCache[$class][$key];
}

if (! method_exists($this, $method = Str::camel($key))) {
return static::$setAttributeMutatorCache[$class][$key] = false;
}

$returnType = (new ReflectionMethod($this, $method))->getReturnType();

return static::$setAttributeMutatorCache[$class][$key] =
$returnType instanceof ReflectionNamedType &&
$returnType->getName() === Attribute::class &&
is_callable($this->{$method}()->set);
}








protected function setMutatedAttributeValue($key, $value)
{
return $this->{'set'.Str::studly($key).'Attribute'}($value);
}








protected function setAttributeMarkedMutatedAttributeValue($key, $value)
{
$attribute = $this->{Str::camel($key)}();

$callback = $attribute->set ?: function ($value) use ($key) {
$this->attributes[$key] = $value;
};

$this->attributes = array_merge(
$this->attributes,
$this->normalizeCastClassResponse(
$key, $callback($value, $this->attributes)
)
);

if ($attribute->withCaching || (is_object($value) && $attribute->withObjectCaching)) {
$this->attributeCastCache[$key] = $value;
} else {
unset($this->attributeCastCache[$key]);
}

return $this;
}







protected function isDateAttribute($key)
{
return in_array($key, $this->getDates(), true) ||
$this->isDateCastable($key);
}








public function fillJsonAttribute($key, $value)
{
[$key, $path] = explode('->', $key, 2);

$value = $this->asJson($this->getArrayAttributeWithValue(
$path, $key, $value
));

$this->attributes[$key] = $this->isEncryptedCastable($key)
? $this->castAttributeAsEncryptedString($key, $value)
: $value;

if ($this->isClassCastable($key)) {
unset($this->classCastCache[$key]);
}

return $this;
}








protected function setClassCastableAttribute($key, $value)
{
$caster = $this->resolveCasterClass($key);

$this->attributes = array_replace(
$this->attributes,
$this->normalizeCastClassResponse($key, $caster->set(
$this, $key, $value, $this->attributes
))
);

if ($caster instanceof CastsInboundAttributes ||
! is_object($value) ||
($caster->withoutObjectCaching ?? false)) {
unset($this->classCastCache[$key]);
} else {
$this->classCastCache[$key] = $value;
}
}








protected function setEnumCastableAttribute($key, $value)
{
$enumClass = $this->getCasts()[$key];

if (! isset($value)) {
$this->attributes[$key] = null;
} elseif (is_object($value)) {
$this->attributes[$key] = $this->getStorableEnumValue($enumClass, $value);
} else {
$this->attributes[$key] = $this->getStorableEnumValue(
$enumClass, $this->getEnumCaseFromValue($enumClass, $value)
);
}
}








protected function getEnumCaseFromValue($enumClass, $value)
{
return is_subclass_of($enumClass, BackedEnum::class)
? $enumClass::from($value)
: constant($enumClass.'::'.$value);
}








protected function getStorableEnumValue($expectedEnum, $value)
{
if (! $value instanceof $expectedEnum) {
throw new ValueError(sprintf('Value [%s] is not of the expected enum type [%s].', var_export($value, true), $expectedEnum));
}

return enum_value($value);
}









protected function getArrayAttributeWithValue($path, $key, $value)
{
return tap($this->getArrayAttributeByKey($key), function (&$array) use ($path, $value) {
Arr::set($array, str_replace('->', '.', $path), $value);
});
}







protected function getArrayAttributeByKey($key)
{
if (! isset($this->attributes[$key])) {
return [];
}

return $this->fromJson(
$this->isEncryptedCastable($key)
? $this->fromEncryptedString($this->attributes[$key])
: $this->attributes[$key]
);
}








protected function castAttributeAsJson($key, $value)
{
$value = $this->asJson($value);

if ($value === false) {
throw JsonEncodingException::forAttribute(
$this, $key, json_last_error_msg()
);
}

return $value;
}







protected function asJson($value)
{
return Json::encode($value);
}








public function fromJson($value, $asObject = false)
{
if ($value === null || $value === '') {
return null;
}

return Json::decode($value, ! $asObject);
}







public function fromEncryptedString($value)
{
return static::currentEncrypter()->decrypt($value, false);
}








protected function castAttributeAsEncryptedString($key, #[\SensitiveParameter] $value)
{
return static::currentEncrypter()->encrypt($value, false);
}







public static function encryptUsing($encrypter)
{
static::$encrypter = $encrypter;
}






protected static function currentEncrypter()
{
return static::$encrypter ?? Crypt::getFacadeRoot();
}








protected function castAttributeAsHashedString($key, #[\SensitiveParameter] $value)
{
if ($value === null) {
return null;
}

if (! Hash::isHashed($value)) {
return Hash::make($value);
}

/**
@phpstan-ignore */
if (! Hash::verifyConfiguration($value)) {
throw new RuntimeException("Could not verify the hashed value's configuration.");
}

return $value;
}







public function fromFloat($value)
{
return match ((string) $value) {
'Infinity' => INF,
'-Infinity' => -INF,
'NaN' => NAN,
default => (float) $value,
};
}








protected function asDecimal($value, $decimals)
{
try {
return (string) BigDecimal::of($value)->toScale($decimals, RoundingMode::HALF_UP);
} catch (BrickMathException $e) {
throw new MathException('Unable to cast value to a decimal.', previous: $e);
}
}







protected function asDate($value)
{
return $this->asDateTime($value)->startOfDay();
}







protected function asDateTime($value)
{



if ($value instanceof CarbonInterface) {
return Date::instance($value);
}




if ($value instanceof DateTimeInterface) {
return Date::parse(
$value->format('Y-m-d H:i:s.u'), $value->getTimezone()
);
}




if (is_numeric($value)) {
return Date::createFromTimestamp($value, date_default_timezone_get());
}




if ($this->isStandardDateFormat($value)) {
return Date::instance(Carbon::createFromFormat('Y-m-d', $value)->startOfDay());
}

$format = $this->getDateFormat();




try {
$date = Date::createFromFormat($format, $value);
} catch (InvalidArgumentException) {
$date = false;
}

return $date ?: Date::parse($value);
}







protected function isStandardDateFormat($value)
{
return preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
}







public function fromDateTime($value)
{
return empty($value) ? $value : $this->asDateTime($value)->format(
$this->getDateFormat()
);
}







protected function asTimestamp($value)
{
return $this->asDateTime($value)->getTimestamp();
}







protected function serializeDate(DateTimeInterface $date)
{
return $date instanceof DateTimeImmutable ?
CarbonImmutable::instance($date)->toJSON() :
Carbon::instance($date)->toJSON();
}






public function getDates()
{
return $this->usesTimestamps() ? [
$this->getCreatedAtColumn(),
$this->getUpdatedAtColumn(),
] : [];
}






public function getDateFormat()
{
return $this->dateFormat ?: $this->getConnection()->getQueryGrammar()->getDateFormat();
}







public function setDateFormat($format)
{
$this->dateFormat = $format;

return $this;
}








public function hasCast($key, $types = null)
{
if (array_key_exists($key, $this->getCasts())) {
return $types ? in_array($this->getCastType($key), (array) $types, true) : true;
}

return false;
}






public function getCasts()
{
if ($this->getIncrementing()) {
return array_merge([$this->getKeyName() => $this->getKeyType()], $this->casts);
}

return $this->casts;
}






protected function casts()
{
return [];
}







protected function isDateCastable($key)
{
return $this->hasCast($key, ['date', 'datetime', 'immutable_date', 'immutable_datetime']);
}







protected function isDateCastableWithCustomFormat($key)
{
return $this->hasCast($key, ['custom_datetime', 'immutable_custom_datetime']);
}







protected function isJsonCastable($key)
{
return $this->hasCast($key, ['array', 'json', 'object', 'collection', 'encrypted:array', 'encrypted:collection', 'encrypted:json', 'encrypted:object']);
}







protected function isEncryptedCastable($key)
{
return $this->hasCast($key, ['encrypted', 'encrypted:array', 'encrypted:collection', 'encrypted:json', 'encrypted:object']);
}









protected function isClassCastable($key)
{
$casts = $this->getCasts();

if (! array_key_exists($key, $casts)) {
return false;
}

$castType = $this->parseCasterClass($casts[$key]);

if (in_array($castType, static::$primitiveCastTypes)) {
return false;
}

if (class_exists($castType)) {
return true;
}

throw new InvalidCastException($this->getModel(), $key, $castType);
}







protected function isEnumCastable($key)
{
$casts = $this->getCasts();

if (! array_key_exists($key, $casts)) {
return false;
}

$castType = $casts[$key];

if (in_array($castType, static::$primitiveCastTypes)) {
return false;
}

return enum_exists($castType);
}









protected function isClassDeviable($key)
{
if (! $this->isClassCastable($key)) {
return false;
}

$castType = $this->resolveCasterClass($key);

return method_exists($castType::class, 'increment') && method_exists($castType::class, 'decrement');
}









protected function isClassSerializable($key)
{
return ! $this->isEnumCastable($key) &&
$this->isClassCastable($key) &&
method_exists($this->resolveCasterClass($key), 'serialize');
}







protected function resolveCasterClass($key)
{
$castType = $this->getCasts()[$key];

$arguments = [];

if (is_string($castType) && str_contains($castType, ':')) {
$segments = explode(':', $castType, 2);

$castType = $segments[0];
$arguments = explode(',', $segments[1]);
}

if (is_subclass_of($castType, Castable::class)) {
$castType = $castType::castUsing($arguments);
}

if (is_object($castType)) {
return $castType;
}

return new $castType(...$arguments);
}







protected function parseCasterClass($class)
{
return ! str_contains($class, ':')
? $class
: explode(':', $class, 2)[0];
}






protected function mergeAttributesFromCachedCasts()
{
$this->mergeAttributesFromClassCasts();
$this->mergeAttributesFromAttributeCasts();
}






protected function mergeAttributesFromClassCasts()
{
foreach ($this->classCastCache as $key => $value) {
$caster = $this->resolveCasterClass($key);

$this->attributes = array_merge(
$this->attributes,
$caster instanceof CastsInboundAttributes
? [$key => $value]
: $this->normalizeCastClassResponse($key, $caster->set($this, $key, $value, $this->attributes))
);
}
}






protected function mergeAttributesFromAttributeCasts()
{
foreach ($this->attributeCastCache as $key => $value) {
$attribute = $this->{Str::camel($key)}();

if ($attribute->get && ! $attribute->set) {
continue;
}

$callback = $attribute->set ?: function ($value) use ($key) {
$this->attributes[$key] = $value;
};

$this->attributes = array_merge(
$this->attributes,
$this->normalizeCastClassResponse(
$key, $callback($value, $this->attributes)
)
);
}
}








protected function normalizeCastClassResponse($key, $value)
{
return is_array($value) ? $value : [$key => $value];
}






public function getAttributes()
{
$this->mergeAttributesFromCachedCasts();

return $this->attributes;
}






protected function getAttributesForInsert()
{
return $this->getAttributes();
}








public function setRawAttributes(array $attributes, $sync = false)
{
$this->attributes = $attributes;

if ($sync) {
$this->syncOriginal();
}

$this->classCastCache = [];
$this->attributeCastCache = [];

return $this;
}








public function getOriginal($key = null, $default = null)
{
return (new static)->setRawAttributes(
$this->original, $sync = true
)->getOriginalWithoutRewindingModel($key, $default);
}








protected function getOriginalWithoutRewindingModel($key = null, $default = null)
{
if ($key) {
return $this->transformModelValue(
$key, Arr::get($this->original, $key, $default)
);
}

return collect($this->original)->mapWithKeys(function ($value, $key) {
return [$key => $this->transformModelValue($key, $value)];
})->all();
}








public function getRawOriginal($key = null, $default = null)
{
return Arr::get($this->original, $key, $default);
}







public function only($attributes)
{
$results = [];

foreach (is_array($attributes) ? $attributes : func_get_args() as $attribute) {
$results[$attribute] = $this->getAttribute($attribute);
}

return $results;
}






public function syncOriginal()
{
$this->original = $this->getAttributes();

return $this;
}







public function syncOriginalAttribute($attribute)
{
return $this->syncOriginalAttributes($attribute);
}







public function syncOriginalAttributes($attributes)
{
$attributes = is_array($attributes) ? $attributes : func_get_args();

$modelAttributes = $this->getAttributes();

foreach ($attributes as $attribute) {
$this->original[$attribute] = $modelAttributes[$attribute];
}

return $this;
}






public function syncChanges()
{
$this->changes = $this->getDirty();

return $this;
}







public function isDirty($attributes = null)
{
return $this->hasChanges(
$this->getDirty(), is_array($attributes) ? $attributes : func_get_args()
);
}







public function isClean($attributes = null)
{
return ! $this->isDirty(...func_get_args());
}






public function discardChanges()
{
[$this->attributes, $this->changes] = [$this->original, []];

return $this;
}







public function wasChanged($attributes = null)
{
return $this->hasChanges(
$this->getChanges(), is_array($attributes) ? $attributes : func_get_args()
);
}








protected function hasChanges($changes, $attributes = null)
{



if (empty($attributes)) {
return count($changes) > 0;
}




foreach (Arr::wrap($attributes) as $attribute) {
if (array_key_exists($attribute, $changes)) {
return true;
}
}

return false;
}






public function getDirty()
{
$dirty = [];

foreach ($this->getAttributes() as $key => $value) {
if (! $this->originalIsEquivalent($key)) {
$dirty[$key] = $value;
}
}

return $dirty;
}






protected function getDirtyForUpdate()
{
return $this->getDirty();
}






public function getChanges()
{
return $this->changes;
}







public function originalIsEquivalent($key)
{
if (! array_key_exists($key, $this->original)) {
return false;
}

$attribute = Arr::get($this->attributes, $key);
$original = Arr::get($this->original, $key);

if ($attribute === $original) {
return true;
} elseif (is_null($attribute)) {
return false;
} elseif ($this->isDateAttribute($key) || $this->isDateCastableWithCustomFormat($key)) {
return $this->fromDateTime($attribute) ===
$this->fromDateTime($original);
} elseif ($this->hasCast($key, ['object', 'collection'])) {
return $this->fromJson($attribute) ===
$this->fromJson($original);
} elseif ($this->hasCast($key, ['real', 'float', 'double'])) {
if ($original === null) {
return false;
}

return abs($this->castAttribute($key, $attribute) - $this->castAttribute($key, $original)) < PHP_FLOAT_EPSILON * 4;
} elseif ($this->isEncryptedCastable($key) && ! empty(static::currentEncrypter()->getPreviousKeys())) {
return false;
} elseif ($this->hasCast($key, static::$primitiveCastTypes)) {
return $this->castAttribute($key, $attribute) ===
$this->castAttribute($key, $original);
} elseif ($this->isClassCastable($key) && Str::startsWith($this->getCasts()[$key], [AsArrayObject::class, AsCollection::class])) {
return $this->fromJson($attribute) === $this->fromJson($original);
} elseif ($this->isClassCastable($key) && Str::startsWith($this->getCasts()[$key], [AsEnumArrayObject::class, AsEnumCollection::class])) {
return $this->fromJson($attribute) === $this->fromJson($original);
} elseif ($this->isClassCastable($key) && $original !== null && Str::startsWith($this->getCasts()[$key], [AsEncryptedArrayObject::class, AsEncryptedCollection::class])) {
if (empty(static::currentEncrypter()->getPreviousKeys())) {
return $this->fromEncryptedString($attribute) === $this->fromEncryptedString($original);
}

return false;
}

return is_numeric($attribute) && is_numeric($original)
&& strcmp((string) $attribute, (string) $original) === 0;
}








protected function transformModelValue($key, $value)
{



if ($this->hasGetMutator($key)) {
return $this->mutateAttribute($key, $value);
} elseif ($this->hasAttributeGetMutator($key)) {
return $this->mutateAttributeMarkedAttribute($key, $value);
}




if ($this->hasCast($key)) {
if (static::preventsAccessingMissingAttributes() &&
! array_key_exists($key, $this->attributes) &&
($this->isEnumCastable($key) ||
in_array($this->getCastType($key), static::$primitiveCastTypes))) {
$this->throwMissingAttributeExceptionIfApplicable($key);
}

return $this->castAttribute($key, $value);
}




if ($value !== null
&& \in_array($key, $this->getDates(), false)) {
return $this->asDateTime($value);
}

return $value;
}







public function append($attributes)
{
$this->appends = array_values(array_unique(
array_merge($this->appends, is_string($attributes) ? func_get_args() : $attributes)
));

return $this;
}






public function getAppends()
{
return $this->appends;
}







public function setAppends(array $appends)
{
$this->appends = $appends;

return $this;
}







public function hasAppended($attribute)
{
return in_array($attribute, $this->appends);
}






public function getMutatedAttributes()
{
if (! isset(static::$mutatorCache[static::class])) {
static::cacheMutatedAttributes($this);
}

return static::$mutatorCache[static::class];
}







public static function cacheMutatedAttributes($classOrInstance)
{
$reflection = new ReflectionClass($classOrInstance);

$class = $reflection->getName();

static::$getAttributeMutatorCache[$class] =
collect($attributeMutatorMethods = static::getAttributeMarkedMutatorMethods($classOrInstance))
->mapWithKeys(function ($match) {
return [lcfirst(static::$snakeAttributes ? Str::snake($match) : $match) => true];
})->all();

static::$mutatorCache[$class] = collect(static::getMutatorMethods($class))
->merge($attributeMutatorMethods)
->map(function ($match) {
return lcfirst(static::$snakeAttributes ? Str::snake($match) : $match);
})->all();
}







protected static function getMutatorMethods($class)
{
preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode(';', get_class_methods($class)), $matches);

return $matches[1];
}







protected static function getAttributeMarkedMutatorMethods($class)
{
$instance = is_object($class) ? $class : new $class;

return collect((new ReflectionClass($instance))->getMethods())->filter(function ($method) use ($instance) {
$returnType = $method->getReturnType();

if ($returnType instanceof ReflectionNamedType &&
$returnType->getName() === Attribute::class) {
if (is_callable($method->invoke($instance)->get)) {
return true;
}
}

return false;
})->map->name->values()->all();
}
}
