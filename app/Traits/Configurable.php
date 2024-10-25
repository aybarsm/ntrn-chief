<?php

namespace App\Traits;

use Illuminate\Support\Arr;

trait Configurable
{
    private static object $_configInstance;

    private function config(): object
    {
        if (! isset(self::$_configInstance)) {
            self::$_configInstance = new class
            {
                private static array $_config = [];

                public static function full(): array
                {
                    return self::$_config;
                }

                public static function __callStatic($method, $parameters)
                {
                    $methodExists = method_exists(Arr::class, $method);
                    if (! $methodExists) {
                        throw new \BadMethodCallException('Method '.__CLASS__."::{$method} does not exist.");
                    }

                    return Arr::{$method}(self::$_config, ...$parameters);
                }

                public function __call($method, $parameters)
                {
                    if (method_exists(__CLASS__, $method)) {
                        return self::{$method}(...$parameters);
                    }

                    return self::__callStatic($method, $parameters);
                }
            };
        }

        return self::$_configInstance;
    }
}
