<?php

declare(strict_types=1);

namespace Pest\Factories\Annotations;

use Pest\Contracts\AddsAnnotations;
use Pest\Factories\TestCaseMethodFactory;

final class TestDox implements AddsAnnotations
{



public function __invoke(TestCaseMethodFactory $method, array $annotations): array
{






assert($method->description !== null);
$methodDescription = str_replace('*/', '{@*}', $method->description);

$annotations[] = "@testdox $methodDescription";

return $annotations;
}
}
