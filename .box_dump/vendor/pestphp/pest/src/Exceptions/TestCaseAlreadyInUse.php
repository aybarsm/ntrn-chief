<?php

declare(strict_types=1);

namespace Pest\Exceptions;

use InvalidArgumentException;
use NunoMaduro\Collision\Contracts\RenderlessEditor;
use NunoMaduro\Collision\Contracts\RenderlessTrace;
use Symfony\Component\Console\Exception\ExceptionInterface;




final class TestCaseAlreadyInUse extends InvalidArgumentException implements ExceptionInterface, RenderlessEditor, RenderlessTrace
{



public function __construct(string $inUse, string $newOne, string $folder)
{
parent::__construct(sprintf('Test case `%s` can not be used. The folder `%s` already uses the test case `%s`',
$newOne, $folder, $inUse));
}
}
