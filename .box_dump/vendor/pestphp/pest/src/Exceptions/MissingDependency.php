<?php

declare(strict_types=1);

namespace Pest\Exceptions;

use InvalidArgumentException;
use NunoMaduro\Collision\Contracts\RenderlessEditor;
use NunoMaduro\Collision\Contracts\RenderlessTrace;
use Symfony\Component\Console\Exception\ExceptionInterface;




final class MissingDependency extends InvalidArgumentException implements ExceptionInterface, RenderlessEditor, RenderlessTrace
{



public function __construct(string $feature, string $dependency)
{
parent::__construct(sprintf('The feature "%s" requires "%s".', $feature, $dependency));
}
}
