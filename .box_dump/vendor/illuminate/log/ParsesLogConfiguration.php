<?php

namespace Illuminate\Log;

use InvalidArgumentException;
use Monolog\Level;

trait ParsesLogConfiguration
{





protected $levels = [
'debug' => Level::Debug,
'info' => Level::Info,
'notice' => Level::Notice,
'warning' => Level::Warning,
'error' => Level::Error,
'critical' => Level::Critical,
'alert' => Level::Alert,
'emergency' => Level::Emergency,
];






abstract protected function getFallbackChannelName();









protected function level(array $config)
{
$level = $config['level'] ?? 'debug';

if (isset($this->levels[$level])) {
return $this->levels[$level];
}

throw new InvalidArgumentException('Invalid log level.');
}









protected function actionLevel(array $config)
{
$level = $config['action_level'] ?? 'debug';

if (isset($this->levels[$level])) {
return $this->levels[$level];
}

throw new InvalidArgumentException('Invalid log action level.');
}







protected function parseChannel(array $config)
{
return $config['name'] ?? $this->getFallbackChannelName();
}
}
