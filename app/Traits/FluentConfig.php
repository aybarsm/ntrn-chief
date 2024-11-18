<?php

namespace App\Traits;

use Illuminate\Support\Fluent;

trait FluentConfig
{
    private Fluent $config;

    protected function config(): Fluent
    {
        if (! isset($this->config)) {
            $this->config = new Fluent;
        }

        return $this->config;
    }
}
