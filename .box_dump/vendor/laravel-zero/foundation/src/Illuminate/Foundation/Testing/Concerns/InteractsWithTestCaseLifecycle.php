<?php

namespace Illuminate\Foundation\Testing\Concerns;

use Carbon\CarbonImmutable;
use Illuminate\Console\Application as Artisan;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Middleware\TrustHosts;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Queue\Queue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Once;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Mockery;
use Mockery\Exception\InvalidCountException;
use PHPUnit\Metadata\Annotation\Parser\Registry as PHPUnitRegistry;
use Throwable;

trait InteractsWithTestCaseLifecycle
{





protected $app;






protected $afterApplicationCreatedCallbacks = [];






protected $beforeApplicationDestroyedCallbacks = [];






protected $callbackException;






protected $setUpHasRun = false;








protected function setUpTheTestEnvironment(): void
{
Facade::clearResolvedInstances();

if (! $this->app) {
$this->refreshApplication();

ParallelTesting::callSetUpTestCaseCallbacks($this);
}

$this->setUpTraits();

foreach ($this->afterApplicationCreatedCallbacks as $callback) {
$callback();
}

Model::setEventDispatcher($this->app['events']);

$this->setUpHasRun = true;
}








protected function tearDownTheTestEnvironment(): void
{
if ($this->app) {
$this->callBeforeApplicationDestroyedCallbacks();

ParallelTesting::callTearDownTestCaseCallbacks($this);

$this->app->flush();

$this->app = null;
}

$this->setUpHasRun = false;

if (property_exists($this, 'serverVariables')) {
$this->serverVariables = [];
}

if (property_exists($this, 'defaultHeaders')) {
$this->defaultHeaders = [];
}

if (class_exists('Mockery')) {
if ($container = Mockery::getContainer()) {
$this->addToAssertionCount($container->mockery_getExpectationCount());
}

try {
Mockery::close();
} catch (InvalidCountException $e) {
if (! Str::contains($e->getMethodName(), ['doWrite', 'askQuestion'])) {
throw $e;
}
}
}

if (class_exists(Carbon::class)) {
Carbon::setTestNow();
}

if (class_exists(CarbonImmutable::class)) {
CarbonImmutable::setTestNow();
}

$this->afterApplicationCreatedCallbacks = [];
$this->beforeApplicationDestroyedCallbacks = [];

if (property_exists($this, 'originalExceptionHandler')) {
$this->originalExceptionHandler = null;
}

if (property_exists($this, 'originalDeprecationHandler')) {
$this->originalDeprecationHandler = null;
}

AboutCommand::flushState();
Artisan::forgetBootstrappers();
Component::flushCache();
Component::forgetComponentsResolver();
Component::forgetFactory();
ConvertEmptyStringsToNull::flushState();
class_exists(EncryptCookies::class) && EncryptCookies::flushState();
HandleExceptions::flushState();
Once::flush();
PreventRequestsDuringMaintenance::flushState();
class_exists(Queue::class) && Queue::createPayloadUsing(null);
RegisterProviders::flushState();
Sleep::fake(false);
TrimStrings::flushState();
class_exists(TrustProxies::class) && TrustProxies::flushState();
class_exists(TrustHosts::class) && TrustHosts::flushState();
ValidateCsrfToken::flushState();

if ($this->callbackException) {
throw $this->callbackException;
}
}






protected function setUpTraits()
{
$uses = array_flip(class_uses_recursive(static::class));

if (isset($uses[RefreshDatabase::class])) {
$this->refreshDatabase();
}

if (isset($uses[DatabaseMigrations::class])) {
$this->runDatabaseMigrations();
}

if (isset($uses[DatabaseTruncation::class])) {
$this->truncateDatabaseTables();
}

if (isset($uses[DatabaseTransactions::class])) {
$this->beginDatabaseTransaction();
}

if (isset($uses[WithoutMiddleware::class])) {
$this->disableMiddlewareForAllTests();
}

if (isset($uses[WithFaker::class])) {
$this->setUpFaker();
}

foreach ($uses as $trait) {
if (method_exists($this, $method = 'setUp'.class_basename($trait))) {
$this->{$method}();
}

if (method_exists($this, $method = 'tearDown'.class_basename($trait))) {
$this->beforeApplicationDestroyed(fn () => $this->{$method}());
}
}

return $uses;
}








public static function tearDownAfterClassUsingTestCase()
{
(function () {
$this->classDocBlocks = [];
$this->methodDocBlocks = [];
})->call(PHPUnitRegistry::getInstance());
}







public function afterApplicationCreated(callable $callback)
{
$this->afterApplicationCreatedCallbacks[] = $callback;

if ($this->setUpHasRun) {
$callback();
}
}







protected function beforeApplicationDestroyed(callable $callback)
{
$this->beforeApplicationDestroyedCallbacks[] = $callback;
}






protected function callBeforeApplicationDestroyedCallbacks()
{
foreach ($this->beforeApplicationDestroyedCallbacks as $callback) {
try {
$callback();
} catch (Throwable $e) {
if (! $this->callbackException) {
$this->callbackException = $e;
}
}
}
}
}
