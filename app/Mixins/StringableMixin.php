<?php

namespace App\Mixins;

use Illuminate\Support\Collection;
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

    public function lines(): \Closure
    {
        return function (int $limit = -1, int $flags = 0, bool $removeEmpty = false): Collection
        {
            $rtr = $removeEmpty ? $this->removeEmptyLines() : $this;

            return $rtr->split("/((\r?\n)|(\r\n?))/", $limit, $flags);
        };
    }

    public static function firstLine(): \Closure
    {
        return function (): Stringable
        {
//            return $this->removeEmptyLines()->split('#\r?\n#', 2, PREG_SPLIT_NO_EMPTY)->first();
            return new static($this->removeEmptyLines()->split('#\r?\n#', 2, PREG_SPLIT_NO_EMPTY)->first());
        };
    }

    public static function makeEmpty(): \Closure
    {
        return function (): Stringable
        {
            return new static('');
        };
    }
}
