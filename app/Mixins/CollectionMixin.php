<?php

namespace App\Mixins;

use Illuminate\Support\Collection;

/** @mixin \Illuminate\Support\Collection */
class CollectionMixin
{
    const string BIND = \Illuminate\Support\Collection::class;

//    public function onlyMatching(): \Closure
//    {
//        return function (string $pattern, ): Collection
//        {
//
//        };
//    }
}
