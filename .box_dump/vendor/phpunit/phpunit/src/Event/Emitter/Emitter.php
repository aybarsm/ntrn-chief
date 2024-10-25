<?php declare(strict_types=1);








namespace PHPUnit\Event;

use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\ComparisonFailure;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\TestSuite\TestSuite;
use PHPUnit\Framework\Constraint;
use PHPUnit\TextUI\Configuration\Configuration;

/**
@no-named-arguments


*/
interface Emitter
{



public function exportObjects(): void;




public function exportsObjects(): bool;

public function applicationStarted(): void;

public function testRunnerStarted(): void;

public function testRunnerConfigured(Configuration $configuration): void;

public function testRunnerBootstrapFinished(string $filename): void;

public function testRunnerLoadedExtensionFromPhar(string $filename, string $name, string $version): void;

/**
@psalm-param
@psalm-param
*/
public function testRunnerBootstrappedExtension(string $className, array $parameters): void;

public function dataProviderMethodCalled(ClassMethod $testMethod, ClassMethod $dataProviderMethod): void;

public function dataProviderMethodFinished(ClassMethod $testMethod, ClassMethod ...$calledMethods): void;

public function testSuiteLoaded(TestSuite $testSuite): void;

public function testSuiteFiltered(TestSuite $testSuite): void;

public function testSuiteSorted(int $executionOrder, int $executionOrderDefects, bool $resolveDependencies): void;

public function testRunnerEventFacadeSealed(): void;

public function testRunnerExecutionStarted(TestSuite $testSuite): void;

public function testRunnerDisabledGarbageCollection(): void;

public function testRunnerTriggeredGarbageCollection(): void;

public function testSuiteSkipped(TestSuite $testSuite, string $message): void;

public function testSuiteStarted(TestSuite $testSuite): void;

public function testPreparationStarted(Code\Test $test): void;

public function testPreparationFailed(Code\Test $test): void;

/**
@psalm-param
*/
public function testBeforeFirstTestMethodCalled(string $testClassName, ClassMethod $calledMethod): void;

/**
@psalm-param
*/
public function testBeforeFirstTestMethodErrored(string $testClassName, ClassMethod $calledMethod, Throwable $throwable): void;

/**
@psalm-param
*/
public function testBeforeFirstTestMethodFinished(string $testClassName, ClassMethod ...$calledMethods): void;

/**
@psalm-param
*/
public function testBeforeTestMethodCalled(string $testClassName, ClassMethod $calledMethod): void;

/**
@psalm-param
*/
public function testBeforeTestMethodFinished(string $testClassName, ClassMethod ...$calledMethods): void;

/**
@psalm-param
*/
public function testPreConditionCalled(string $testClassName, ClassMethod $calledMethod): void;

/**
@psalm-param
*/
public function testPreConditionFinished(string $testClassName, ClassMethod ...$calledMethods): void;

public function testPrepared(Code\Test $test): void;

/**
@psalm-param
*/
public function testRegisteredComparator(string $className): void;




public function testAssertionSucceeded(mixed $value, Constraint\Constraint $constraint, string $message): void;




public function testAssertionFailed(mixed $value, Constraint\Constraint $constraint, string $message): void;

/**
@psalm-param
*/
public function testCreatedMockObject(string $className): void;

/**
@psalm-param
*/
public function testCreatedMockObjectForIntersectionOfInterfaces(array $interfaces): void;

/**
@psalm-param
*/
public function testCreatedMockObjectForTrait(string $traitName): void;

/**
@psalm-param
*/
public function testCreatedMockObjectForAbstractClass(string $className): void;

/**
@psalm-param
@psalm-param
*/
public function testCreatedMockObjectFromWsdl(string $wsdlFile, string $originalClassName, string $mockClassName, array $methods, bool $callOriginalConstructor, array $options): void;

/**
@psalm-param
*/
public function testCreatedPartialMockObject(string $className, string ...$methodNames): void;

/**
@psalm-param
*/
public function testCreatedTestProxy(string $className, array $constructorArguments): void;

/**
@psalm-param
*/
public function testCreatedStub(string $className): void;

/**
@psalm-param
*/
public function testCreatedStubForIntersectionOfInterfaces(array $interfaces): void;

public function testErrored(Code\Test $test, Throwable $throwable): void;

public function testFailed(Code\Test $test, Throwable $throwable, ?ComparisonFailure $comparisonFailure): void;

public function testPassed(Code\Test $test): void;

/**
@psalm-param
*/
public function testConsideredRisky(Code\Test $test, string $message): void;

public function testMarkedAsIncomplete(Code\Test $test, Throwable $throwable): void;

/**
@psalm-param
*/
public function testSkipped(Code\Test $test, string $message): void;

/**
@psalm-param
*/
public function testTriggeredPhpunitDeprecation(Code\Test $test, string $message): void;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function testTriggeredPhpDeprecation(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline, bool $ignoredByTest): void;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function testTriggeredDeprecation(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline, bool $ignoredByTest): void;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function testTriggeredError(Code\Test $test, string $message, string $file, int $line, bool $suppressed): void;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function testTriggeredNotice(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline): void;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function testTriggeredPhpNotice(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline): void;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function testTriggeredWarning(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline): void;

/**
@psalm-param
@psalm-param
@psalm-param
*/
public function testTriggeredPhpWarning(Code\Test $test, string $message, string $file, int $line, bool $suppressed, bool $ignoredByBaseline): void;

/**
@psalm-param
*/
public function testTriggeredPhpunitError(Code\Test $test, string $message): void;

/**
@psalm-param
*/
public function testTriggeredPhpunitWarning(Code\Test $test, string $message): void;

/**
@psalm-param
*/
public function testPrintedUnexpectedOutput(string $output): void;

public function testFinished(Code\Test $test, int $numberOfAssertionsPerformed): void;

/**
@psalm-param
*/
public function testPostConditionCalled(string $testClassName, ClassMethod $calledMethod): void;

/**
@psalm-param
*/
public function testPostConditionFinished(string $testClassName, ClassMethod ...$calledMethods): void;

/**
@psalm-param
*/
public function testAfterTestMethodCalled(string $testClassName, ClassMethod $calledMethod): void;

/**
@psalm-param
*/
public function testAfterTestMethodFinished(string $testClassName, ClassMethod ...$calledMethods): void;

/**
@psalm-param
*/
public function testAfterLastTestMethodCalled(string $testClassName, ClassMethod $calledMethod): void;

/**
@psalm-param
*/
public function testAfterLastTestMethodFinished(string $testClassName, ClassMethod ...$calledMethods): void;

public function testSuiteFinished(TestSuite $testSuite): void;

public function testRunnerTriggeredDeprecation(string $message): void;

public function testRunnerTriggeredWarning(string $message): void;

public function testRunnerEnabledGarbageCollection(): void;

public function testRunnerExecutionAborted(): void;

public function testRunnerExecutionFinished(): void;

public function testRunnerFinished(): void;

public function applicationFinished(int $shellExitCode): void;
}
