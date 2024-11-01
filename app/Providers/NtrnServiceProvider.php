<?php

namespace App\Providers;

use App\Contracts\Services\Console\IndicatorContract;
use App\Attributes\Console\CommandTask;
use App\Services\Console\Indicator;
use App\Services\Ntrn;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class NtrnServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        App::booted(function () {
            Command::mixin(new \App\Mixins\CommandMixin(), true);
            Command::forgetTask();
            Ntrn::init('PromptTheme');
        });

        $this->app->bind(IndicatorContract::class, Indicator::class);
    }

    public function boot(): void
    {
        Stringable::mixin(new \App\Mixins\StringableMixin(), true);
        Str::mixin(new \App\Mixins\StrMixin(), true);
    }

    protected function registerMixins(): void
    {

    }
}
