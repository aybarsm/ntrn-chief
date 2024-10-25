<?php

declare(strict_types=1);

namespace Faker\Container;

use Faker\Extension\Extension;

/**
@experimental


*/
final class Container implements ContainerInterface
{



private array $definitions;

private array $services = [];







public function __construct(array $definitions)
{
$this->definitions = $definitions;
}











public function get($id): Extension
{
if (!is_string($id)) {
throw new \InvalidArgumentException(sprintf(
'First argument of %s::get() must be string',
self::class,
));
}

if (array_key_exists($id, $this->services)) {
return $this->services[$id];
}

if (!$this->has($id)) {
throw new NotInContainerException(sprintf(
'There is not service with id "%s" in the container.',
$id,
));
}

$definition = $this->definitions[$id];

$service = $this->getService($id, $definition);

if (!$service instanceof Extension) {
throw new \RuntimeException(sprintf(
'Service resolved for identifier "%s" does not implement the %s" interface.',
$id,
Extension::class,
));
}

$this->services[$id] = $service;

return $service;
}






private function getService(string $id, $definition)
{
if (is_callable($definition)) {
try {
return $definition();
} catch (\Throwable $e) {
throw new ContainerException(
sprintf('Error while invoking callable for "%s"', $id),
0,
$e,
);
}
} elseif (is_object($definition)) {
return $definition;
} elseif (is_string($definition)) {
if (class_exists($definition)) {
try {
return new $definition();
} catch (\Throwable $e) {
throw new ContainerException(sprintf('Could not instantiate class "%s"', $id), 0, $e);
}
}

throw new ContainerException(sprintf(
'Could not instantiate class "%s". Class was not found.',
$id,
));
} else {
throw new ContainerException(sprintf(
'Invalid type for definition with id "%s"',
$id,
));
}
}








public function has($id): bool
{
if (!is_string($id)) {
throw new \InvalidArgumentException(sprintf(
'First argument of %s::get() must be string',
self::class,
));
}

return array_key_exists($id, $this->definitions);
}
}
