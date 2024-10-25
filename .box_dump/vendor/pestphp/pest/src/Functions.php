<?php

declare(strict_types=1);

use Pest\Concerns\Expectable;
use Pest\Exceptions\AfterAllWithinDescribe;
use Pest\Exceptions\BeforeAllWithinDescribe;
use Pest\Expectation;
use Pest\PendingCalls\AfterEachCall;
use Pest\PendingCalls\BeforeEachCall;
use Pest\PendingCalls\DescribeCall;
use Pest\PendingCalls\TestCall;
use Pest\PendingCalls\UsesCall;
use Pest\Repositories\DatasetsRepository;
use Pest\Support\Backtrace;
use Pest\Support\DatasetInfo;
use Pest\Support\HigherOrderTapProxy;
use Pest\TestSuite;
use PHPUnit\Framework\TestCase;

if (! function_exists('expect')) {
/**
@template





*/
function expect(mixed $value = null): Expectation
{
return new Expectation($value);
}
}

if (! function_exists('beforeAll')) {



function beforeAll(Closure $closure): void
{
if (! is_null(DescribeCall::describing())) {
$filename = Backtrace::file();

throw new BeforeAllWithinDescribe($filename);
}

TestSuite::getInstance()->beforeAll->set($closure);
}
}

if (! function_exists('beforeEach')) {





function beforeEach(?Closure $closure = null): BeforeEachCall
{
$filename = Backtrace::file();

return new BeforeEachCall(TestSuite::getInstance(), $filename, $closure);
}
}

if (! function_exists('dataset')) {





function dataset(string $name, Closure|iterable $dataset): void
{
$scope = DatasetInfo::scope(Backtrace::datasetsFile());

DatasetsRepository::set($name, $dataset, $scope);
}
}

if (! function_exists('describe')) {







function describe(string $description, Closure $tests): DescribeCall
{
$filename = Backtrace::testFile();

return new DescribeCall(TestSuite::getInstance(), $filename, $description, $tests);
}
}

if (! function_exists('uses')) {






function uses(string ...$classAndTraits): UsesCall
{
$filename = Backtrace::file();

return new UsesCall($filename, array_values($classAndTraits));
}
}

if (! function_exists('test')) {







function test(?string $description = null, ?Closure $closure = null): HigherOrderTapProxy|TestCall
{
if ($description === null && TestSuite::getInstance()->test instanceof \PHPUnit\Framework\TestCase) {
return new HigherOrderTapProxy(TestSuite::getInstance()->test);
}

$filename = Backtrace::testFile();

return new TestCall(TestSuite::getInstance(), $filename, $description, $closure);
}
}

if (! function_exists('it')) {







function it(string $description, ?Closure $closure = null): TestCall
{
$description = sprintf('it %s', $description);


$test = test($description, $closure);

return $test;
}
}

if (! function_exists('todo')) {







function todo(string $description): TestCall
{
$test = test($description);

assert($test instanceof TestCall);

return $test->todo();
}
}

if (! function_exists('afterEach')) {





function afterEach(?Closure $closure = null): AfterEachCall
{
$filename = Backtrace::file();

return new AfterEachCall(TestSuite::getInstance(), $filename, $closure);
}
}

if (! function_exists('afterAll')) {



function afterAll(Closure $closure): void
{
if (! is_null(DescribeCall::describing())) {
$filename = Backtrace::file();

throw new AfterAllWithinDescribe($filename);
}

TestSuite::getInstance()->afterAll->set($closure);
}
}
