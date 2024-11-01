<?php

namespace App\Mixins;

use Illuminate\Support\Stringable;

/** @mixin \Illuminate\Support\Stringable */
class StringableMixin
{
    public function removeEmptyLines(): \Closure
    {
        return function (): Stringable {
            return $this->replaceMatches('/^\s*[\r\n]+|[\r\n]+\s*\z/', '')
                ->replaceMatches('/(\n\s*){2,}/', "\n");
        };
    }
}
