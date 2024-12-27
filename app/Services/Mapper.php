<?php

namespace App\Services;

use App\Enums\MapperReturn;

class Mapper
{
    public function __construct(
        protected MapperReturn $return,
        protected string $key = '',
        protected string $value = '',
        protected string $returnSrc = '',
    )
    {
        //
    }

    public static function make(MapperReturn $return, string $key = '', string $value = '', string $returnSource = ''): static
    {
        return new static($return, $key, $value, $returnSource);
    }

    public function result
}
