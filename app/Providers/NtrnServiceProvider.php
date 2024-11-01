<?php

namespace App\Providers;

use App\Services\Ntrn;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class NtrnServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        App::booted(function () {
            Ntrn::init('PromptTheme');
        });
    }

    public function boot(): void
    {
        Stringable::mixin(new \App\Mixins\StringableMixin, true);
        Str::mixin(new \App\Mixins\StrMixin, true);
    }
}
