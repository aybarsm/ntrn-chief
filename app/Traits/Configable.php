<?php

namespace App\Traits;

use Illuminate\Support\Arr;

Trait Configable
{
    private array $configables = [];

    protected function config(string $method, ...$parameters)
    {
        throw_unless(method_exists(Arr::class, $method), new \BadMethodCallException("Method [{$method}] does not exist in [Arr::class]."));

        return Arr::{$method}($this->configables, ...$parameters);
    }

}
