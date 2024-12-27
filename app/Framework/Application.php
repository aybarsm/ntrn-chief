<?php

namespace App\Framework;

use Illuminate\Support\Fluent;
use LaravelZero\Framework\Application as LaravelZeroApplication;

class Application extends LaravelZeroApplication
{
    public function version(): string
    {
        return "{$this['config']->get('app.version')} ({$this['config']->get('app.build')})";
    }
}
