<?php

declare(strict_types=1);

namespace Faker\Extension;

use Faker\Generator;

/**
@experimental
*/
interface GeneratorAwareExtension extends Extension
{





public function withGenerator(Generator $generator): Extension;
}
