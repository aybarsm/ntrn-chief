<?php

declare(strict_types=1);

namespace Pest\Contracts;

use Pest\Factories\TestCaseMethodFactory;




interface AddsAnnotations
{






public function __invoke(TestCaseMethodFactory $method, array $annotations): array;
}
