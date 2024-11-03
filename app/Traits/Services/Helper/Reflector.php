<?php

namespace App\Traits\Services\Helper;

use Illuminate\Support\Arr;

trait Reflector
{
    public static function getMethodParameters(string $class, string $method): array
    {
        return (new \ReflectionMethod($class, $method))->getParameters();
    }

    public static function getMethodParameterPosition(string $class, string $method, string $parameter): ?int
    {
        return Arr::first(static::getMethodParameters($class, $method), fn ($param) => $param->getName() === $parameter)?->getPosition();
    }
}
