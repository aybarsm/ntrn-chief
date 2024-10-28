<?php

namespace App\Providers;

use App\Contracts\Services\Console\IndicatorContract;
use App\Services\Console\Indicator;
use App\Services\Ntrn;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class NtrnServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        App::booted(function () {
            Ntrn::init('PromptTheme');
        });

        $this->app->bind(IndicatorContract::class, Indicator::class);

//        $this->app->whenHasAttribute(Commandtask::class, function (...$params){
//            dump('asdasds');
//            dump($params);
//        });
    }

    public function boot(): void
    {

    }

    protected function registerMixins(): void
    {

    }
}
