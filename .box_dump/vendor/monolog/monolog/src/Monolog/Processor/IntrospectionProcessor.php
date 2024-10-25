<?php declare(strict_types=1);










namespace Monolog\Processor;

use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LogLevel;
use Monolog\LogRecord;












class IntrospectionProcessor implements ProcessorInterface
{
private Level $level;


private array $skipClassesPartials;

private int $skipStackFramesCount;

private const SKIP_FUNCTIONS = [
'call_user_func',
'call_user_func_array',
];

/**
@phpstan-param



*/
public function __construct(int|string|Level $level = Level::Debug, array $skipClassesPartials = [], int $skipStackFramesCount = 0)
{
$this->level = Logger::toMonologLevel($level);
$this->skipClassesPartials = array_merge(['Monolog\\'], $skipClassesPartials);
$this->skipStackFramesCount = $skipStackFramesCount;
}




public function __invoke(LogRecord $record): LogRecord
{

if ($record->level->isLowerThan($this->level)) {
return $record;
}

$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);


array_shift($trace);

array_shift($trace);

$i = 0;

while ($this->isTraceClassOrSkippedFunction($trace, $i)) {
if (isset($trace[$i]['class'])) {
foreach ($this->skipClassesPartials as $part) {
if (strpos($trace[$i]['class'], $part) !== false) {
$i++;

continue 2;
}
}
} elseif (\in_array($trace[$i]['function'], self::SKIP_FUNCTIONS, true)) {
$i++;

continue;
}

break;
}

$i += $this->skipStackFramesCount;


$record->extra = array_merge(
$record->extra,
[
'file' => $trace[$i - 1]['file'] ?? null,
'line' => $trace[$i - 1]['line'] ?? null,
'class' => $trace[$i]['class'] ?? null,
'callType' => $trace[$i]['type'] ?? null,
'function' => $trace[$i]['function'] ?? null,
]
);

return $record;
}




private function isTraceClassOrSkippedFunction(array $trace, int $index): bool
{
if (!isset($trace[$index])) {
return false;
}

return isset($trace[$index]['class']) || \in_array($trace[$index]['function'], self::SKIP_FUNCTIONS, true);
}
}
