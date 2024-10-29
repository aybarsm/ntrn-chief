<?php

namespace App\Providers;

use App\Contracts\Services\Console\IndicatorContract;
use App\Attributes\Console\CommandTask;
use App\Services\Console\Indicator;
use App\Services\Ntrn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

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

    }

    protected function registerMixins(): void
    {

    }
}
