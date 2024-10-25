<?php

namespace Illuminate\Log;

use Closure;
use Illuminate\Log\Context\Repository as ContextRepository;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Logger as Monolog;
use Monolog\Processor\ProcessorInterface;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;
use Throwable;

/**
@mixin
*/
class LogManager implements LoggerInterface
{
use ParsesLogConfiguration;






protected $app;






protected $channels = [];






protected $sharedContext = [];






protected $customCreators = [];






protected $dateFormat = 'Y-m-d H:i:s';







public function __construct($app)
{
$this->app = $app;
}







public function build(array $config)
{
unset($this->channels['ondemand']);

return $this->get('ondemand', $config);
}








public function stack(array $channels, $channel = null)
{
return (new Logger(
$this->createStackDriver(compact('channels', 'channel')),
$this->app['events']
))->withContext($this->sharedContext);
}







public function channel($channel = null)
{
return $this->driver($channel);
}







public function driver($driver = null)
{
return $this->get($this->parseDriver($driver));
}








protected function get($name, ?array $config = null)
{
try {
return $this->channels[$name] ?? with($this->resolve($name, $config), function ($logger) use ($name) {
$loggerWithContext = $this->tap(
$name,
new Logger($logger, $this->app['events'])
)->withContext($this->sharedContext);

if (method_exists($loggerWithContext->getLogger(), 'pushProcessor')) {
$loggerWithContext->pushProcessor(function ($record) {
if (! $this->app->bound(ContextRepository::class)) {
return $record;
}

return $record->with(extra: [
...$record->extra,
...$this->app[ContextRepository::class]->all(),
]);
});
}

return $this->channels[$name] = $loggerWithContext;
});
} catch (Throwable $e) {
return tap($this->createEmergencyLogger(), function ($logger) use ($e) {
$logger->emergency('Unable to create configured logger. Using emergency logger.', [
'exception' => $e,
]);
});
}
}








protected function tap($name, Logger $logger)
{
foreach ($this->configurationFor($name)['tap'] ?? [] as $tap) {
[$class, $arguments] = $this->parseTap($tap);

$this->app->make($class)->__invoke($logger, ...explode(',', $arguments));
}

return $logger;
}







protected function parseTap($tap)
{
return str_contains($tap, ':') ? explode(':', $tap, 2) : [$tap, ''];
}






protected function createEmergencyLogger()
{
$config = $this->configurationFor('emergency');

$handler = new StreamHandler(
$config['path'] ?? $this->app->storagePath().'/logs/laravel.log',
$this->level(['level' => 'debug'])
);

return new Logger(
new Monolog('laravel', $this->prepareHandlers([$handler])),
$this->app['events']
);
}










protected function resolve($name, ?array $config = null)
{
$config ??= $this->configurationFor($name);

if (is_null($config)) {
throw new InvalidArgumentException("Log [{$name}] is not defined.");
}

if (isset($this->customCreators[$config['driver']])) {
return $this->callCustomCreator($config);
}

$driverMethod = 'create'.ucfirst($config['driver']).'Driver';

if (method_exists($this, $driverMethod)) {
return $this->{$driverMethod}($config);
}

throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
}







protected function callCustomCreator(array $config)
{
return $this->customCreators[$config['driver']]($this->app, $config);
}







protected function createCustomDriver(array $config)
{
$factory = is_callable($via = $config['via']) ? $via : $this->app->make($via);

return $factory($config);
}







protected function createStackDriver(array $config)
{
if (is_string($config['channels'])) {
$config['channels'] = explode(',', $config['channels']);
}

$handlers = collect($config['channels'])->flatMap(function ($channel) {
return $channel instanceof LoggerInterface
? $channel->getHandlers()
: $this->channel($channel)->getHandlers();
})->all();

$processors = collect($config['channels'])->flatMap(function ($channel) {
return $channel instanceof LoggerInterface
? $channel->getProcessors()
: $this->channel($channel)->getProcessors();
})->all();

if ($config['ignore_exceptions'] ?? false) {
$handlers = [new WhatFailureGroupHandler($handlers)];
}

return new Monolog($this->parseChannel($config), $handlers, $processors);
}







protected function createSingleDriver(array $config)
{
return new Monolog($this->parseChannel($config), [
$this->prepareHandler(
new StreamHandler(
$config['path'], $this->level($config),
$config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
), $config
),
], $config['replace_placeholders'] ?? false ? [new PsrLogMessageProcessor()] : []);
}







protected function createDailyDriver(array $config)
{
return new Monolog($this->parseChannel($config), [
$this->prepareHandler(new RotatingFileHandler(
$config['path'], $config['days'] ?? 7, $this->level($config),
$config['bubble'] ?? true, $config['permission'] ?? null, $config['locking'] ?? false
), $config),
], $config['replace_placeholders'] ?? false ? [new PsrLogMessageProcessor()] : []);
}







protected function createSlackDriver(array $config)
{
return new Monolog($this->parseChannel($config), [
$this->prepareHandler(new SlackWebhookHandler(
$config['url'],
$config['channel'] ?? null,
$config['username'] ?? 'Laravel',
$config['attachment'] ?? true,
$config['emoji'] ?? ':boom:',
$config['short'] ?? false,
$config['context'] ?? true,
$this->level($config),
$config['bubble'] ?? true,
$config['exclude_fields'] ?? []
), $config),
], $config['replace_placeholders'] ?? false ? [new PsrLogMessageProcessor()] : []);
}







protected function createSyslogDriver(array $config)
{
return new Monolog($this->parseChannel($config), [
$this->prepareHandler(new SyslogHandler(
Str::snake($this->app['config']['app.name'], '-'),
$config['facility'] ?? LOG_USER, $this->level($config)
), $config),
], $config['replace_placeholders'] ?? false ? [new PsrLogMessageProcessor()] : []);
}







protected function createErrorlogDriver(array $config)
{
return new Monolog($this->parseChannel($config), [
$this->prepareHandler(new ErrorLogHandler(
$config['type'] ?? ErrorLogHandler::OPERATING_SYSTEM, $this->level($config)
)),
], $config['replace_placeholders'] ?? false ? [new PsrLogMessageProcessor()] : []);
}










protected function createMonologDriver(array $config)
{
if (! is_a($config['handler'], HandlerInterface::class, true)) {
throw new InvalidArgumentException(
$config['handler'].' must be an instance of '.HandlerInterface::class
);
}

collect($config['processors'] ?? [])->each(function ($processor) {
$processor = $processor['processor'] ?? $processor;

if (! is_a($processor, ProcessorInterface::class, true)) {
throw new InvalidArgumentException(
$processor.' must be an instance of '.ProcessorInterface::class
);
}
});

$with = array_merge(
['level' => $this->level($config)],
$config['with'] ?? [],
$config['handler_with'] ?? []
);

$handler = $this->prepareHandler(
$this->app->make($config['handler'], $with), $config
);

$processors = collect($config['processors'] ?? [])
->map(fn ($processor) => $this->app->make($processor['processor'] ?? $processor, $processor['with'] ?? []))
->toArray();

return new Monolog(
$this->parseChannel($config),
[$handler],
$processors,
);
}







protected function prepareHandlers(array $handlers)
{
foreach ($handlers as $key => $handler) {
$handlers[$key] = $this->prepareHandler($handler);
}

return $handlers;
}








protected function prepareHandler(HandlerInterface $handler, array $config = [])
{
if (isset($config['action_level'])) {
$handler = new FingersCrossedHandler(
$handler,
$this->actionLevel($config),
0,
true,
$config['stop_buffering'] ?? true
);
}

if (! $handler instanceof FormattableHandlerInterface) {
return $handler;
}

if (! isset($config['formatter'])) {
$handler->setFormatter($this->formatter());
} elseif ($config['formatter'] !== 'default') {
$handler->setFormatter($this->app->make($config['formatter'], $config['formatter_with'] ?? []));
}

return $handler;
}






protected function formatter()
{
return new LineFormatter(null, $this->dateFormat, true, true, true);
}







public function shareContext(array $context)
{
foreach ($this->channels as $channel) {
$channel->withContext($context);
}

$this->sharedContext = array_merge($this->sharedContext, $context);

return $this;
}






public function sharedContext()
{
return $this->sharedContext;
}






public function withoutContext()
{
foreach ($this->channels as $channel) {
if (method_exists($channel, 'withoutContext')) {
$channel->withoutContext();
}
}

return $this;
}






public function flushSharedContext()
{
$this->sharedContext = [];

return $this;
}






protected function getFallbackChannelName()
{
return $this->app->bound('env') ? $this->app->environment() : 'production';
}







protected function configurationFor($name)
{
return $this->app['config']["logging.channels.{$name}"];
}






public function getDefaultDriver()
{
return $this->app['config']['logging.default'];
}







public function setDefaultDriver($name)
{
$this->app['config']['logging.default'] = $name;
}








public function extend($driver, Closure $callback)
{
$this->customCreators[$driver] = $callback->bindTo($this, $this);

return $this;
}







public function forgetChannel($driver = null)
{
$driver = $this->parseDriver($driver);

if (isset($this->channels[$driver])) {
unset($this->channels[$driver]);
}
}







protected function parseDriver($driver)
{
$driver ??= $this->getDefaultDriver();

if ($this->app->runningUnitTests()) {
$driver ??= 'null';
}

return $driver;
}






public function getChannels()
{
return $this->channels;
}








public function emergency($message, array $context = []): void
{
$this->driver()->emergency($message, $context);
}











public function alert($message, array $context = []): void
{
$this->driver()->alert($message, $context);
}










public function critical($message, array $context = []): void
{
$this->driver()->critical($message, $context);
}









public function error($message, array $context = []): void
{
$this->driver()->error($message, $context);
}











public function warning($message, array $context = []): void
{
$this->driver()->warning($message, $context);
}








public function notice($message, array $context = []): void
{
$this->driver()->notice($message, $context);
}










public function info($message, array $context = []): void
{
$this->driver()->info($message, $context);
}








public function debug($message, array $context = []): void
{
$this->driver()->debug($message, $context);
}









public function log($level, $message, array $context = []): void
{
$this->driver()->log($level, $message, $context);
}







public function setApplication($app)
{
$this->app = $app;

return $this;
}








public function __call($method, $parameters)
{
return $this->driver()->$method(...$parameters);
}
}
