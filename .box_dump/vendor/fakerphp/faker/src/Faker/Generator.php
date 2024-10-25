<?php

namespace Faker;

use Faker\Container\ContainerInterface;






































































































































































































































































































































































































































































































































































class Generator
{
protected $providers = [];
protected $formatters = [];

private $container;




private $uniqueGenerator;

public function __construct(ContainerInterface $container = null)
{
$this->container = $container ?: Container\ContainerBuilder::withDefaultExtensions()->build();
}

/**
@template






*/
public function ext(string $id): Extension\Extension
{
if (!$this->container->has($id)) {
throw new Extension\ExtensionNotFound(sprintf(
'No Faker extension with id "%s" was loaded.',
$id,
));
}

$extension = $this->container->get($id);

if ($extension instanceof Extension\GeneratorAwareExtension) {
$extension = $extension->withGenerator($this);
}

return $extension;
}

public function addProvider($provider)
{
array_unshift($this->providers, $provider);

$this->formatters = [];
}

public function getProviders()
{
return $this->providers;
}


















public function unique($reset = false, $maxRetries = 10000)
{
if ($reset || $this->uniqueGenerator === null) {
$this->uniqueGenerator = new UniqueGenerator($this, $maxRetries);
}

return $this->uniqueGenerator;
}








public function optional(float $weight = 0.5, $default = null)
{
if ($weight > 1) {
trigger_deprecation('fakerphp/faker', '1.16', 'First argument ($weight) to method "optional()" must be between 0 and 1. You passed %f, we assume you meant %f.', $weight, $weight / 100);
$weight = $weight / 100;
}

return new ChanceGenerator($this, $weight, $default);
}


























public function valid(?\Closure $validator = null, int $maxRetries = 10000)
{
return new ValidGenerator($this, $validator, $maxRetries);
}

public function seed($seed = null)
{
if ($seed === null) {
mt_srand();
} else {
mt_srand((int) $seed, self::mode());
}
}




private static function mode(): int
{
if (PHP_VERSION_ID < 80300) {
return MT_RAND_PHP;
}

return MT_RAND_MT19937;
}

public function format($format, $arguments = [])
{
return call_user_func_array($this->getFormatter($format), $arguments);
}






public function getFormatter($format)
{
if (isset($this->formatters[$format])) {
return $this->formatters[$format];
}

if (method_exists($this, $format)) {
$this->formatters[$format] = [$this, $format];

return $this->formatters[$format];
}


if (preg_match('|^([a-zA-Z0-9\\\]+)->([a-zA-Z0-9]+)$|', $format, $matches)) {
$this->formatters[$format] = [$this->ext($matches[1]), $matches[2]];

return $this->formatters[$format];
}

foreach ($this->providers as $provider) {
if (method_exists($provider, $format)) {
$this->formatters[$format] = [$provider, $format];

return $this->formatters[$format];
}
}

throw new \InvalidArgumentException(sprintf('Unknown format "%s"', $format));
}








public function parse($string)
{
$callback = function ($matches) {
return $this->format($matches[1]);
};

return preg_replace_callback('/{{\s?(\w+|[\w\\\]+->\w+?)\s?}}/u', $callback, $string);
}






public function mimeType()
{
return $this->ext(Extension\FileExtension::class)->mimeType();
}






public function fileExtension()
{
return $this->ext(Extension\FileExtension::class)->extension();
}




public function filePath()
{
return $this->ext(Extension\FileExtension::class)->filePath();
}






public function bloodType(): string
{
return $this->ext(Extension\BloodExtension::class)->bloodType();
}






public function bloodRh(): string
{
return $this->ext(Extension\BloodExtension::class)->bloodRh();
}






public function bloodGroup(): string
{
return $this->ext(Extension\BloodExtension::class)->bloodGroup();
}






public function ean13(): string
{
return $this->ext(Extension\BarcodeExtension::class)->ean13();
}






public function ean8(): string
{
return $this->ext(Extension\BarcodeExtension::class)->ean8();
}








public function isbn10(): string
{
return $this->ext(Extension\BarcodeExtension::class)->isbn10();
}








public function isbn13(): string
{
return $this->ext(Extension\BarcodeExtension::class)->isbn13();
}






public function numberBetween($int1 = 0, $int2 = 2147483647): int
{
return $this->ext(Extension\NumberExtension::class)->numberBetween((int) $int1, (int) $int2);
}




public function randomDigit(): int
{
return $this->ext(Extension\NumberExtension::class)->randomDigit();
}




public function randomDigitNot($except): int
{
return $this->ext(Extension\NumberExtension::class)->randomDigitNot((int) $except);
}




public function randomDigitNotZero(): int
{
return $this->ext(Extension\NumberExtension::class)->randomDigitNotZero();
}






public function randomFloat($nbMaxDecimals = null, $min = 0, $max = null): float
{
return $this->ext(Extension\NumberExtension::class)->randomFloat(
$nbMaxDecimals !== null ? (int) $nbMaxDecimals : null,
(float) $min,
$max !== null ? (float) $max : null,
);
}











public function randomNumber($nbDigits = null, $strict = false): int
{
return $this->ext(Extension\NumberExtension::class)->randomNumber(
$nbDigits !== null ? (int) $nbDigits : null,
(bool) $strict,
);
}











public function semver(bool $preRelease = false, bool $build = false): string
{
return $this->ext(Extension\VersionExtension::class)->semver($preRelease, $build);
}




protected function callFormatWithMatches($matches)
{
trigger_deprecation('fakerphp/faker', '1.14', 'Protected method "callFormatWithMatches()" is deprecated and will be removed.');

return $this->format($matches[1]);
}






public function __get($attribute)
{
trigger_deprecation('fakerphp/faker', '1.14', 'Accessing property "%s" is deprecated, use "%s()" instead.', $attribute, $attribute);

return $this->format($attribute);
}





public function __call($method, $attributes)
{
return $this->format($method, $attributes);
}

public function __destruct()
{
$this->seed();
}

public function __wakeup()
{
$this->formatters = [];
}
}
