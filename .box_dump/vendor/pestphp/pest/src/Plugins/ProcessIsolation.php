<?php

declare(strict_types=1);

namespace Pest\Plugins;

use Pest\Contracts\Plugins\HandlesArguments;
use Pest\Exceptions\InvalidOption;




final class ProcessIsolation implements HandlesArguments
{
use Concerns\HandleArguments;




public function handleArguments(array $arguments): array
{
if ($this->hasArgument('--process-isolation', $arguments)) {
throw new InvalidOption('The [--process-isolation] option is not supported.');
}

return $arguments;
}
}
