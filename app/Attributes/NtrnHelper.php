<?php

namespace App\Attributes;

use App\Services\Helper;
use Attribute;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class NtrnHelper implements ContextualAttribute
{
    public array $params;

    public function __construct(public string $method, array ...$params)
    {
        $this->params = $params;
    }
    public static function resolve(self $attribute, Container $container)
    {
        throw_if(
            ! method_exists(Helper::class, $attribute->method),
            new \Exception("Method {$attribute->method} does not exist on the App\Services\Helper class.")
        );

        return Helper::{$attribute->method}(...$attribute->params);
    }
}
