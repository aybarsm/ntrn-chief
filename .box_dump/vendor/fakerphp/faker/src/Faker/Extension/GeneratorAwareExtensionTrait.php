<?php

declare(strict_types=1);

namespace Faker\Extension;

use Faker\Generator;




trait GeneratorAwareExtensionTrait
{



private $generator;




public function withGenerator(Generator $generator): Extension
{
$instance = clone $this;

$instance->generator = $generator;

return $instance;
}
}
