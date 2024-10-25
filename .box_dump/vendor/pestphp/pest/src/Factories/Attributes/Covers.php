<?php

declare(strict_types=1);

namespace Pest\Factories\Attributes;

use Pest\Factories\Covers\CoversClass;
use Pest\Factories\Covers\CoversFunction;
use Pest\Factories\TestCaseMethodFactory;




final class Covers extends Attribute
{



public static bool $above = true;







public function __invoke(TestCaseMethodFactory $method, array $attributes): array
{
foreach ($method->covers as $covering) {
if ($covering instanceof CoversClass) {

if (str_contains($covering->class, '\\')) {
$covering->class = '\\'.$covering->class;
}

$attributes[] = "#[\PHPUnit\Framework\Attributes\CoversClass({$covering->class}::class)]";
} elseif ($covering instanceof CoversFunction) {
$attributes[] = "#[\PHPUnit\Framework\Attributes\CoversFunction('{$covering->function}')]";
}
}

return $attributes;
}
}
