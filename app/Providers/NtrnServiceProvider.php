<?php

namespace App\Providers;

use App\Traits\Configable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class NtrnServiceProvider extends ServiceProvider
{
    use Configable;

    public function register(): void
    {
        App::booted(function () {
            static::initPromptTheme();
        });
    }

    public function boot(): void
    {
        $this->loadMixins();
    }

    /** @noinspection PhpUnreachableStatementInspection */
    protected function addMixin(string $mixin): void
    {
        throw_if(blank($mixin), new \Exception('Mixin class name cannot be empty.'));

        $cnfKey = str($mixin)
            ->replace('\\', '_')
            ->lower()
            ->prepend('mixins.loaded.')
            ->value();

        if ($this->config('has', $cnfKey)) {
            return;
        }

        throw_if(! class_exists($mixin), "Mixin class [{$mixin}] not found.");

        $docComment = (new \ReflectionClass($mixin))?->getDocComment();
        throw_if($docComment === false, "Mixin class [{$mixin}] does not have a doc comment.");

        $bind = Str::match(config('ntrn.mixins.pattern', '/@mixin\s*([^\s*]+)/'), $docComment);
        throw_if(blank($bind), "Mixin class [{$mixin}] does not have a pattern eligible bind.");
        throw_if(! class_exists($bind), "Mixin [{$mixin}] class bind of [{$bind}] not found.");
        throw_if(! method_exists($bind, 'mixin'), "Mixin [{$mixin}] class bind of [{$bind}] does not have a mixin method.");

        $bind::mixin(new $mixin, (bool) config('ntrn.mixins.replace', true));
        $this->config('set', $cnfKey, true);
    }

    protected function loadMixins(): void
    {
        foreach (config('ntrn.mixins.list', []) as $mixin) {
            $this->addMixin($mixin);
        }
    }

    protected static function initPromptTheme(): void
    {
        $default = \App\Prompts\Prompt::getTheme('default');

        $ntrn = Arr::where(Arr::mapWithKeys($default, function ($renderer, $prompt) {
            $prompt = Str::replace('Laravel\\Prompts\\', 'App\\Prompts\\', $prompt);
            $renderer = Str::replace('Laravel\\Prompts\\Themes\\Default\\', 'App\\Prompts\\Themes\\Ntrn\\', $renderer);

            return [$prompt => $renderer];
        }), fn ($renderer, $prompt) => class_exists($renderer) && class_exists($prompt));

        $additional = [
            \App\Prompts\Running::class => \App\Prompts\Themes\Ntrn\RunningRenderer::class,
        ];

        \App\Prompts\Prompt::addTheme('ntrn', array_merge($ntrn, $additional));

        \App\Prompts\Prompt::theme('ntrn');
    }
}
