<?php declare(strict_types=1);








namespace PHPUnit\Metadata;

use PHPUnit\Metadata\Version\Requirement;

/**
@psalm-immutable
@no-named-arguments

*/
abstract class Metadata
{
private const CLASS_LEVEL = 0;
private const METHOD_LEVEL = 1;

/**
@psalm-var
*/
private readonly int $level;

public static function after(): After
{
return new After(self::METHOD_LEVEL);
}

public static function afterClass(): AfterClass
{
return new AfterClass(self::METHOD_LEVEL);
}

public static function backupGlobalsOnClass(bool $enabled): BackupGlobals
{
return new BackupGlobals(self::CLASS_LEVEL, $enabled);
}

public static function backupGlobalsOnMethod(bool $enabled): BackupGlobals
{
return new BackupGlobals(self::METHOD_LEVEL, $enabled);
}

public static function backupStaticPropertiesOnClass(bool $enabled): BackupStaticProperties
{
return new BackupStaticProperties(self::CLASS_LEVEL, $enabled);
}

public static function backupStaticPropertiesOnMethod(bool $enabled): BackupStaticProperties
{
return new BackupStaticProperties(self::METHOD_LEVEL, $enabled);
}

public static function before(): Before
{
return new Before(self::METHOD_LEVEL);
}

public static function beforeClass(): BeforeClass
{
return new BeforeClass(self::METHOD_LEVEL);
}

/**
@psalm-param
*/
public static function coversClass(string $className): CoversClass
{
return new CoversClass(self::CLASS_LEVEL, $className);
}

/**
@psalm-param
*/
public static function coversFunction(string $functionName): CoversFunction
{
return new CoversFunction(self::CLASS_LEVEL, $functionName);
}

/**
@psalm-param
*/
public static function coversOnClass(string $target): Covers
{
return new Covers(self::CLASS_LEVEL, $target);
}

/**
@psalm-param
*/
public static function coversOnMethod(string $target): Covers
{
return new Covers(self::METHOD_LEVEL, $target);
}

/**
@psalm-param
*/
public static function coversDefaultClass(string $className): CoversDefaultClass
{
return new CoversDefaultClass(self::CLASS_LEVEL, $className);
}

public static function coversNothingOnClass(): CoversNothing
{
return new CoversNothing(self::CLASS_LEVEL);
}

public static function coversNothingOnMethod(): CoversNothing
{
return new CoversNothing(self::METHOD_LEVEL);
}

/**
@psalm-param
@psalm-param
*/
public static function dataProvider(string $className, string $methodName): DataProvider
{
return new DataProvider(self::METHOD_LEVEL, $className, $methodName);
}

/**
@psalm-param
*/
public static function dependsOnClass(string $className, bool $deepClone, bool $shallowClone): DependsOnClass
{
return new DependsOnClass(self::METHOD_LEVEL, $className, $deepClone, $shallowClone);
}

/**
@psalm-param
@psalm-param
*/
public static function dependsOnMethod(string $className, string $methodName, bool $deepClone, bool $shallowClone): DependsOnMethod
{
return new DependsOnMethod(self::METHOD_LEVEL, $className, $methodName, $deepClone, $shallowClone);
}

public static function doesNotPerformAssertionsOnClass(): DoesNotPerformAssertions
{
return new DoesNotPerformAssertions(self::CLASS_LEVEL);
}

public static function doesNotPerformAssertionsOnMethod(): DoesNotPerformAssertions
{
return new DoesNotPerformAssertions(self::METHOD_LEVEL);
}

/**
@psalm-param
*/
public static function excludeGlobalVariableFromBackupOnClass(string $globalVariableName): ExcludeGlobalVariableFromBackup
{
return new ExcludeGlobalVariableFromBackup(self::CLASS_LEVEL, $globalVariableName);
}

/**
@psalm-param
*/
public static function excludeGlobalVariableFromBackupOnMethod(string $globalVariableName): ExcludeGlobalVariableFromBackup
{
return new ExcludeGlobalVariableFromBackup(self::METHOD_LEVEL, $globalVariableName);
}

/**
@psalm-param
@psalm-param
*/
public static function excludeStaticPropertyFromBackupOnClass(string $className, string $propertyName): ExcludeStaticPropertyFromBackup
{
return new ExcludeStaticPropertyFromBackup(self::CLASS_LEVEL, $className, $propertyName);
}

/**
@psalm-param
@psalm-param
*/
public static function excludeStaticPropertyFromBackupOnMethod(string $className, string $propertyName): ExcludeStaticPropertyFromBackup
{
return new ExcludeStaticPropertyFromBackup(self::METHOD_LEVEL, $className, $propertyName);
}

/**
@psalm-param
*/
public static function groupOnClass(string $groupName): Group
{
return new Group(self::CLASS_LEVEL, $groupName);
}

/**
@psalm-param
*/
public static function groupOnMethod(string $groupName): Group
{
return new Group(self::METHOD_LEVEL, $groupName);
}

public static function ignoreDeprecationsOnClass(): IgnoreDeprecations
{
return new IgnoreDeprecations(self::CLASS_LEVEL);
}

public static function ignoreDeprecationsOnMethod(): IgnoreDeprecations
{
return new IgnoreDeprecations(self::METHOD_LEVEL);
}

/**
@psalm-param
*/
public static function ignoreClassForCodeCoverage(string $className): IgnoreClassForCodeCoverage
{
return new IgnoreClassForCodeCoverage(self::CLASS_LEVEL, $className);
}

/**
@psalm-param
@psalm-param
*/
public static function ignoreMethodForCodeCoverage(string $className, string $methodName): IgnoreMethodForCodeCoverage
{
return new IgnoreMethodForCodeCoverage(self::CLASS_LEVEL, $className, $methodName);
}

/**
@psalm-param
*/
public static function ignoreFunctionForCodeCoverage(string $functionName): IgnoreFunctionForCodeCoverage
{
return new IgnoreFunctionForCodeCoverage(self::CLASS_LEVEL, $functionName);
}

public static function postCondition(): PostCondition
{
return new PostCondition(self::METHOD_LEVEL);
}

public static function preCondition(): PreCondition
{
return new PreCondition(self::METHOD_LEVEL);
}

public static function preserveGlobalStateOnClass(bool $enabled): PreserveGlobalState
{
return new PreserveGlobalState(self::CLASS_LEVEL, $enabled);
}

public static function preserveGlobalStateOnMethod(bool $enabled): PreserveGlobalState
{
return new PreserveGlobalState(self::METHOD_LEVEL, $enabled);
}

/**
@psalm-param
*/
public static function requiresFunctionOnClass(string $functionName): RequiresFunction
{
return new RequiresFunction(self::CLASS_LEVEL, $functionName);
}

/**
@psalm-param
*/
public static function requiresFunctionOnMethod(string $functionName): RequiresFunction
{
return new RequiresFunction(self::METHOD_LEVEL, $functionName);
}

/**
@psalm-param
@psalm-param
*/
public static function requiresMethodOnClass(string $className, string $methodName): RequiresMethod
{
return new RequiresMethod(self::CLASS_LEVEL, $className, $methodName);
}

/**
@psalm-param
@psalm-param
*/
public static function requiresMethodOnMethod(string $className, string $methodName): RequiresMethod
{
return new RequiresMethod(self::METHOD_LEVEL, $className, $methodName);
}

/**
@psalm-param
*/
public static function requiresOperatingSystemOnClass(string $operatingSystem): RequiresOperatingSystem
{
return new RequiresOperatingSystem(self::CLASS_LEVEL, $operatingSystem);
}

/**
@psalm-param
*/
public static function requiresOperatingSystemOnMethod(string $operatingSystem): RequiresOperatingSystem
{
return new RequiresOperatingSystem(self::METHOD_LEVEL, $operatingSystem);
}

/**
@psalm-param
*/
public static function requiresOperatingSystemFamilyOnClass(string $operatingSystemFamily): RequiresOperatingSystemFamily
{
return new RequiresOperatingSystemFamily(self::CLASS_LEVEL, $operatingSystemFamily);
}

/**
@psalm-param
*/
public static function requiresOperatingSystemFamilyOnMethod(string $operatingSystemFamily): RequiresOperatingSystemFamily
{
return new RequiresOperatingSystemFamily(self::METHOD_LEVEL, $operatingSystemFamily);
}

public static function requiresPhpOnClass(Requirement $versionRequirement): RequiresPhp
{
return new RequiresPhp(self::CLASS_LEVEL, $versionRequirement);
}

public static function requiresPhpOnMethod(Requirement $versionRequirement): RequiresPhp
{
return new RequiresPhp(self::METHOD_LEVEL, $versionRequirement);
}

/**
@psalm-param
*/
public static function requiresPhpExtensionOnClass(string $extension, ?Requirement $versionRequirement): RequiresPhpExtension
{
return new RequiresPhpExtension(self::CLASS_LEVEL, $extension, $versionRequirement);
}

/**
@psalm-param
*/
public static function requiresPhpExtensionOnMethod(string $extension, ?Requirement $versionRequirement): RequiresPhpExtension
{
return new RequiresPhpExtension(self::METHOD_LEVEL, $extension, $versionRequirement);
}

public static function requiresPhpunitOnClass(Requirement $versionRequirement): RequiresPhpunit
{
return new RequiresPhpunit(self::CLASS_LEVEL, $versionRequirement);
}

public static function requiresPhpunitOnMethod(Requirement $versionRequirement): RequiresPhpunit
{
return new RequiresPhpunit(self::METHOD_LEVEL, $versionRequirement);
}

/**
@psalm-param
@psalm-param
*/
public static function requiresSettingOnClass(string $setting, string $value): RequiresSetting
{
return new RequiresSetting(self::CLASS_LEVEL, $setting, $value);
}

/**
@psalm-param
@psalm-param
*/
public static function requiresSettingOnMethod(string $setting, string $value): RequiresSetting
{
return new RequiresSetting(self::METHOD_LEVEL, $setting, $value);
}

public static function runClassInSeparateProcess(): RunClassInSeparateProcess
{
return new RunClassInSeparateProcess(self::CLASS_LEVEL);
}

public static function runTestsInSeparateProcesses(): RunTestsInSeparateProcesses
{
return new RunTestsInSeparateProcesses(self::CLASS_LEVEL);
}

public static function runInSeparateProcess(): RunInSeparateProcess
{
return new RunInSeparateProcess(self::METHOD_LEVEL);
}

public static function test(): Test
{
return new Test(self::METHOD_LEVEL);
}

/**
@psalm-param
*/
public static function testDoxOnClass(string $text): TestDox
{
return new TestDox(self::CLASS_LEVEL, $text);
}

/**
@psalm-param
*/
public static function testDoxOnMethod(string $text): TestDox
{
return new TestDox(self::METHOD_LEVEL, $text);
}

public static function testWith(array $data): TestWith
{
return new TestWith(self::METHOD_LEVEL, $data);
}

/**
@psalm-param
*/
public static function usesClass(string $className): UsesClass
{
return new UsesClass(self::CLASS_LEVEL, $className);
}

/**
@psalm-param
*/
public static function usesFunction(string $functionName): UsesFunction
{
return new UsesFunction(self::CLASS_LEVEL, $functionName);
}

/**
@psalm-param
*/
public static function usesOnClass(string $target): Uses
{
return new Uses(self::CLASS_LEVEL, $target);
}

/**
@psalm-param
*/
public static function usesOnMethod(string $target): Uses
{
return new Uses(self::METHOD_LEVEL, $target);
}

/**
@psalm-param
*/
public static function usesDefaultClass(string $className): UsesDefaultClass
{
return new UsesDefaultClass(self::CLASS_LEVEL, $className);
}

public static function withoutErrorHandler(): WithoutErrorHandler
{
return new WithoutErrorHandler(self::METHOD_LEVEL);
}

/**
@psalm-param
*/
protected function __construct(int $level)
{
$this->level = $level;
}

public function isClassLevel(): bool
{
return $this->level === self::CLASS_LEVEL;
}

public function isMethodLevel(): bool
{
return $this->level === self::METHOD_LEVEL;
}

/**
@psalm-assert-if-true
*/
public function isAfter(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isAfterClass(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isBackupGlobals(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isBackupStaticProperties(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isBeforeClass(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isBefore(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isCovers(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isCoversClass(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isCoversDefaultClass(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isCoversFunction(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isCoversNothing(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isDataProvider(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isDependsOnClass(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isDependsOnMethod(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isDoesNotPerformAssertions(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isExcludeGlobalVariableFromBackup(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isExcludeStaticPropertyFromBackup(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isGroup(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isIgnoreDeprecations(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isIgnoreClassForCodeCoverage(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isIgnoreMethodForCodeCoverage(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isIgnoreFunctionForCodeCoverage(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRunClassInSeparateProcess(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRunInSeparateProcess(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRunTestsInSeparateProcesses(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isTest(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isPreCondition(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isPostCondition(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isPreserveGlobalState(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRequiresMethod(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRequiresFunction(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRequiresOperatingSystem(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRequiresOperatingSystemFamily(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRequiresPhp(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRequiresPhpExtension(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRequiresPhpunit(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isRequiresSetting(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isTestDox(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isTestWith(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isUses(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isUsesClass(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isUsesDefaultClass(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isUsesFunction(): bool
{
return false;
}

/**
@psalm-assert-if-true
*/
public function isWithoutErrorHandler(): bool
{
return false;
}
}
