<?php

declare(strict_types=1);

namespace Pest\Exceptions;

use InvalidArgumentException;
use NunoMaduro\Collision\Contracts\RenderlessEditor;
use NunoMaduro\Collision\Contracts\RenderlessTrace;
use Symfony\Component\Console\Exception\ExceptionInterface;




final class InvalidPestCommand extends InvalidArgumentException implements ExceptionInterface, RenderlessEditor, RenderlessTrace
{



public function __construct()
{
parent::__construct('Please run [./vendor/bin/pest] instead.');
}
}