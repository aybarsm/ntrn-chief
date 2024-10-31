<?php

namespace App\Traits;

use App\Services\Helper;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

Trait Configable
{
    private array $configables = [];

    protected static function validateConfigMethod(string $method): void
    {
        throw_if(! method_exists(Arr::class, $method), new \BadMethodCallException("Method [{$method}] does not exist in [Arr::class]."));
    }

    protected function configable(string $method, ...$parameters): mixed
    {
        static::validateConfigMethod($method);

        if (isset($this->configablePrefix) && is_string($this->configablePrefix) && ! blank($this->configablePrefix) and array_key_exists(0, $parameters) and is_string($parameters[0])){
            $parameters[0] = Str::start($parameters[0], Str::of("{$this->configablePrefix}")->trim()->trim('.')->append('.')->value());
        }

        if ($method === 'get' && is_array($parameters[0]) && Arr::isList($parameters[0]) && ! blank($parameters[0])){

            $defaultEmpty = Helper::generateExtendedUlid();
            $default = Arr::exists($parameters, 1) ? $parameters[1] : null;
            $retrieved = $defaultEmpty;

            foreach($parameters[0] as $path){
                $retrieved = Arr::get($this->configables, $path, $defaultEmpty);
                if ($retrieved !== $defaultEmpty){
                    break;
                }
            }

            return $retrieved !== $defaultEmpty ? $retrieved : $default;
        }

        return Arr::{$method}($this->configables, ...$parameters);
    }

    protected function config(string $method, ...$parameters): mixed
    {
        return $this->configable($method, ...$parameters);
    }

}
