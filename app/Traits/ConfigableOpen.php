<?php

namespace App\Traits;

Trait ConfigableOpen
{
    use Configable;

    public function config(string $method, ...$parameters): mixed
    {
        return $this->configable($method, ...$parameters);
    }
}
