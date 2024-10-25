<?php declare(strict_types=1);










namespace Monolog;

use Psr\Log\LogLevel;

















enum Level: int
{



case Debug = 100;






case Info = 200;




case Notice = 250;







case Warning = 300;




case Error = 400;






case Critical = 500;







case Alert = 550;




case Emergency = 600;





public static function fromName(string $name): self
{
return match ($name) {
'debug', 'Debug', 'DEBUG' => self::Debug,
'info', 'Info', 'INFO' => self::Info,
'notice', 'Notice', 'NOTICE' => self::Notice,
'warning', 'Warning', 'WARNING' => self::Warning,
'error', 'Error', 'ERROR' => self::Error,
'critical', 'Critical', 'CRITICAL' => self::Critical,
'alert', 'Alert', 'ALERT' => self::Alert,
'emergency', 'Emergency', 'EMERGENCY' => self::Emergency,
};
}





public static function fromValue(int $value): self
{
return self::from($value);
}




public function includes(Level $level): bool
{
return $this->value <= $level->value;
}

public function isHigherThan(Level $level): bool
{
return $this->value > $level->value;
}

public function isLowerThan(Level $level): bool
{
return $this->value < $level->value;
}








public function getName(): string
{
return match ($this) {
self::Debug => 'DEBUG',
self::Info => 'INFO',
self::Notice => 'NOTICE',
self::Warning => 'WARNING',
self::Error => 'ERROR',
self::Critical => 'CRITICAL',
self::Alert => 'ALERT',
self::Emergency => 'EMERGENCY',
};
}

/**
@phpstan-return


*/
public function toPsrLogLevel(): string
{
return match ($this) {
self::Debug => LogLevel::DEBUG,
self::Info => LogLevel::INFO,
self::Notice => LogLevel::NOTICE,
self::Warning => LogLevel::WARNING,
self::Error => LogLevel::ERROR,
self::Critical => LogLevel::CRITICAL,
self::Alert => LogLevel::ALERT,
self::Emergency => LogLevel::EMERGENCY,
};
}

/**
@phpstan-return


*/
public function toRFC5424Level(): int
{
return match ($this) {
self::Debug => 7,
self::Info => 6,
self::Notice => 5,
self::Warning => 4,
self::Error => 3,
self::Critical => 2,
self::Alert => 1,
self::Emergency => 0,
};
}

public const VALUES = [
100,
200,
250,
300,
400,
500,
550,
600,
];

public const NAMES = [
'DEBUG',
'INFO',
'NOTICE',
'WARNING',
'ERROR',
'CRITICAL',
'ALERT',
'EMERGENCY',
];
}
