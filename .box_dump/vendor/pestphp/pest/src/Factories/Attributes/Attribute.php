<?php

declare(strict_types=1);

namespace Pest\Factories\Attributes;

use Pest\Factories\TestCaseMethodFactory;




abstract class Attribute
{



public static bool $above = false;





public function __invoke(TestCaseMethodFactory $method, array $attributes): array
{
return $attributes;
}
}
