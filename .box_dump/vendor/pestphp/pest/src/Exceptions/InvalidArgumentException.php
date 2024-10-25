<?php

declare(strict_types=1);

namespace Pest\Exceptions;

use InvalidArgumentException as BaseInvalidArgumentException;
use NunoMaduro\Collision\Contracts\RenderlessEditor;
use NunoMaduro\Collision\Contracts\RenderlessTrace;
use Symfony\Component\Console\Exception\ExceptionInterface;




final class InvalidArgumentException extends BaseInvalidArgumentException implements ExceptionInterface, RenderlessEditor, RenderlessTrace
{



public function __construct(string $message)
{
parent::__construct($message, 1);
}
}
