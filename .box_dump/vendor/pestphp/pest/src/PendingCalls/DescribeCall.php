<?php

declare(strict_types=1);

namespace Pest\PendingCalls;

use Closure;
use Pest\Support\Backtrace;
use Pest\TestSuite;




final class DescribeCall
{



private static ?string $describing = null;




public function __construct(
public readonly TestSuite $testSuite,
public readonly string $filename,
public readonly string $description,
public readonly Closure $tests
) {

}




public static function describing(): ?string
{
return self::$describing;
}




public function __destruct()
{
self::$describing = $this->description;

try {
($this->tests)();
} finally {
self::$describing = null;
}
}






public function __call(string $name, array $arguments): BeforeEachCall
{
$filename = Backtrace::file();

$beforeEachCall = new BeforeEachCall(TestSuite::getInstance(), $filename);

$beforeEachCall->describing = $this->description;

return $beforeEachCall->{$name}(...$arguments); 
}
}
