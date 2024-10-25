<?php declare(strict_types=1);










namespace Monolog;

use InvalidArgumentException;























class Registry
{





private static array $loggers = [];









public static function addLogger(Logger $logger, ?string $name = null, bool $overwrite = false): void
{
$name = $name ?? $logger->getName();

if (isset(self::$loggers[$name]) && !$overwrite) {
throw new InvalidArgumentException('Logger with the given name already exists');
}

self::$loggers[$name] = $logger;
}






public static function hasLogger($logger): bool
{
if ($logger instanceof Logger) {
$index = array_search($logger, self::$loggers, true);

return false !== $index;
}

return isset(self::$loggers[$logger]);
}






public static function removeLogger($logger): void
{
if ($logger instanceof Logger) {
if (false !== ($idx = array_search($logger, self::$loggers, true))) {
unset(self::$loggers[$idx]);
}
} else {
unset(self::$loggers[$logger]);
}
}




public static function clear(): void
{
self::$loggers = [];
}







public static function getInstance(string $name): Logger
{
if (!isset(self::$loggers[$name])) {
throw new InvalidArgumentException(sprintf('Requested "%s" logger instance is not in the registry', $name));
}

return self::$loggers[$name];
}









public static function __callStatic(string $name, array $arguments): Logger
{
return self::getInstance($name);
}
}
