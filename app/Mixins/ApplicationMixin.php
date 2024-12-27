<?php

namespace App\Mixins;
use Illuminate\Support\Fluent;

/** @mixin \App\Framework\Application */
class ApplicationMixin
{
    const string BIND = \App\Framework\Application::class;

//    public static function conf(): \Closure
//    {
//        return function (): Fluent
//        {
//
//        };
//    }
}
