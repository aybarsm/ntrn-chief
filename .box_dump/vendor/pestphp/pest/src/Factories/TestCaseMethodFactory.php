<?php

declare(strict_types=1);

namespace Pest\Factories;

use Closure;
use Pest\Contracts\AddsAnnotations;
use Pest\Exceptions\ShouldNotHappen;
use Pest\Factories\Concerns\HigherOrderable;
use Pest\Repositories\DatasetsRepository;
use Pest\Support\Str;
use Pest\TestSuite;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;




final class TestCaseMethodFactory
{
use HigherOrderable;




public ?string $describing = null;




public int $repetitions = 1;




public bool $todo = false;






public array $datasets = [];






public array $depends = [];






public array $groups = [];






public array $covers = [];




public function __construct(
public string $filename,
public ?string $description,
public ?Closure $closure,
) {
$this->closure ??= function (): void {
(Assert::getCount() > 0 || $this->doesNotPerformAssertions()) ?: self::markTestIncomplete(); 
};

$this->bootHigherOrderable();
}




public function getClosure(TestCase $concrete): Closure
{
$concrete::flush(); 

if ($this->description === null) {
throw ShouldNotHappen::fromMessage('Description can not be empty.');
}

$closure = $this->closure;

$testCase = TestSuite::getInstance()->tests->get($this->filename);

$testCase->factoryProxies->proxy($concrete);
$this->factoryProxies->proxy($concrete);

$method = $this;

return function () use ($testCase, $method, $closure): mixed { 

$testCase->proxies->proxy($this);
$method->proxies->proxy($this);

$testCase->chains->chain($this);
$method->chains->chain($this);

return \Pest\Support\Closure::bind($closure, $this, self::class)(...func_get_args());
};
}




public function receivesArguments(): bool
{
return $this->datasets !== [] || $this->depends !== [];
}







public function buildForEvaluation(array $annotationsToUse, array $attributesToUse): string
{
if ($this->description === null) {
throw ShouldNotHappen::fromMessage('The test description may not be empty.');
}

$methodName = Str::evaluable($this->description);

$datasetsCode = '';
$annotations = ['@test'];
$attributes = [];

foreach ($annotationsToUse as $annotation) {
$annotations = (new $annotation)->__invoke($this, $annotations);
}

foreach ($attributesToUse as $attribute) {
$attributes = (new $attribute)->__invoke($this, $attributes);
}

if ($this->datasets !== [] || $this->repetitions > 1) {
$dataProviderName = $methodName.'_dataset';
$annotations[] = "@dataProvider $dataProviderName";
$datasetsCode = $this->buildDatasetForEvaluation($methodName, $dataProviderName);
}

$annotations = implode('', array_map(
static fn (string $annotation): string => sprintf("\n     * %s", $annotation), $annotations,
));

$attributes = implode('', array_map(
static fn (string $attribute): string => sprintf("\n        %s", $attribute), $attributes,
));

return <<<PHP

                /**$annotations
                 */
                $attributes
                public function $methodName()
                {
                    \$test = \Pest\TestSuite::getInstance()->tests->get(self::\$__filename)->getMethod(\$this->name())->getClosure(\$this);

                    return \$this->__runTest(
                        \$test,
                        ...func_get_args(),
                    );
                }
                $datasetsCode
            PHP;
}




private function buildDatasetForEvaluation(string $methodName, string $dataProviderName): string
{
$datasets = $this->datasets;

if ($this->repetitions > 1) {
$datasets = [range(1, $this->repetitions), ...$datasets];
}

DatasetsRepository::with($this->filename, $methodName, $datasets);

return <<<EOF

                public static function $dataProviderName()
                {
                    return __PestDatasets::get(self::\$__filename, "$methodName");
                }

        EOF;
}
}
