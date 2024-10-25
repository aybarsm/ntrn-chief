<?php

declare(strict_types=1);

namespace Faker\Container;

use Psr\Container\NotFoundExceptionInterface;

/**
@experimental
*/
final class NotInContainerException extends \RuntimeException implements NotFoundExceptionInterface
{
}
