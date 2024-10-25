<?php

declare(strict_types=1);

namespace Faker\Container;

use Psr\Container\ContainerExceptionInterface;

/**
@experimental
*/
final class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
}
