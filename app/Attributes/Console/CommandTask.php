<?php

namespace App\Attributes\Console;

use Attribute;
use App\Enums\IndicatorType;

//use Illuminate\Contracts\Container\Container;

#[Attribute(Attribute::TARGET_METHOD)]
class CommandTask
{
    public function __construct(public int $order, public string $title, public ?IndicatorType $indicator = null)
    {

    }

//    public static function resolve(self $attribute, Container $container)
//    {
//        dump('Commandtask::resolve');
//        return [];
//    }
}
