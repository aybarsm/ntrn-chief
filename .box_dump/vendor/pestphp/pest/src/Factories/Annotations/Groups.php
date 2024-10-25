<?php

declare(strict_types=1);

namespace Pest\Factories\Annotations;

use Pest\Contracts\AddsAnnotations;
use Pest\Factories\TestCaseMethodFactory;




final class Groups implements AddsAnnotations
{



public function __invoke(TestCaseMethodFactory $method, array $annotations): array
{
foreach ($method->groups as $group) {
$annotations[] = "@group $group";
}

return $annotations;
}
}
