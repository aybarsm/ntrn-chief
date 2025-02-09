<?php

namespace App\Providers;

use App\Framework\Component\Finder;
use App\Traits\Configable;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class NtrnServiceProvider extends ServiceProvider
{
    use Configable;

    public function register(): void
    {
        $this->loadConfigs();
        $this->registerBindings();
        $this->onBooted();
        $this->onTerminating();
    }

    public function boot(): void
    {
        $this->loadMixins();
        $this->loadViews();
        $this->loadMigrations();
        Event::listen(function (ScheduledTaskStarting $taskStarting) {
            Log::info('Scheduled Task Starting', ['command' => $taskStarting->task->command]);
        });
        //        Event::listen(function (ScheduledTaskStarting $taskStarting) {
        //            Context::add('app.scheduled.task.starting', true);
        //            Context::add('app.scheduled.task.command', $taskStarting->task->command);
        //        });
        //        Event::listen('*', function (string $eventName, array $data) {
        //            $list = Context::get('app.events', []);
        //            if (! in_array($eventName, $list)) {
        //                $list[] = $eventName;
        //                Context::add('app.events', $list);
        //            }
        //        });
    }

    protected function onBooted(): void
    {
        $this->app->booted(function () {
            static::initPromptTheme();
            static::initValidatorExtensions();
            DB::timestampAwareTransactions();
        });
    }

    protected function onTerminating(): void
    {
        $this->app->terminating(function (...$params): void {
            if (! $this->isConfCacheEligible()) {
                return;
            }

            Cache::store(config('ntrn.conf.cache.store'))
                ->rememberForever(
                    key: config('ntrn.conf.cache.key'),
                    callback: fn () => serialize(app('conf'))
                );
        });
    }

    protected function isConfCacheEligible(): bool
    {
        $key = config('ntrn.conf.cache.key');
        $store = config('ntrn.conf.cache.store');

        if (blank($store) || blank($key)) {
            return false;
        }

        try {
            Cache::store($store);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(
            'conf',
            function (Application $app): Fluent {
                if ($this->isConfCacheEligible()) {
                    return unserialize(Cache::store(config('ntrn.conf.cache.store'))
                        ->get(
                            key: config('ntrn.conf.cache.key'),
                            default: serialize(new Fluent))
                    );
                }

                return new Fluent;
            }
        );

        $this->app->bind(
            'git.branch',
            function (Application $app) {
                $process = Process::fromShellCommandline(
                    'git symbolic-ref --short HEAD',
                    $app->basePath()
                );

                $process->run();

                return trim($process->getOutput());
            }
        );
    }

    protected function loadMixins(): void
    {
        foreach (config('ntrn.mixins.list', []) as $mixin) {
            $this->addMixin($mixin);
        }
    }

    protected function isConfigLoadEligible(string $key): string|false
    {
        $path = config(Str::of($key)->trim()->trim('.')->start('ntrn.')->value(), '');

        return ! blank($path) && file_exists($path) && is_dir($path) ? $path : false;
    }

    protected function loadConfigs(): void
    {
        if (($path = $this->isConfigLoadEligible('ntrn.configs')) === false) {
            return;
        }

        $files = Finder::create()
            ->files()
            ->in($path)
            ->depth('== 0')
            ->name('*.php');

        foreach ($files as $file) {
            $key = Str::of($file->getFilenameWithoutExtension())
                ->trim()
                ->trim('.')
                ->start('ntrn.')
                ->value();
            $this->mergeConfigFrom($file->getRealPath(), $key);
        }
    }

    protected function loadViews(): void
    {
        if (($path = $this->isConfigLoadEligible('ntrn.views')) === false) {
            return;
        }

        $this->loadViewsFrom($path, 'ntrn');
    }

    protected function loadMigrations(): void
    {
        if (($path = $this->isConfigLoadEligible('ntrn.migrations')) === false) {
            return;
        }

        $this->loadMigrationsFrom($path);
    }

    /** @noinspection PhpUnreachableStatementInspection */
    protected function addMixin(string $mixin): void
    {
        throw_if(blank($mixin), 'Mixin class name cannot be empty.');

        $cnfKey = str($mixin)
            ->replace('\\', '_')
            ->lower()
            ->prepend('mixins.loaded.')
            ->value();

        if ($this->config('has', $cnfKey)) {
            return;
        }

        throw_if(! class_exists($mixin), "Mixin class [{$mixin}] not found.");

        throw_if(! defined("{$mixin}::BIND"), "Mixin class [{$mixin}] does not have a bind property.");
        $bind = $mixin::BIND;
        throw_if(blank($bind), "Mixin class [{$mixin}] bind property is empty.");
        throw_if(! class_exists($bind), "Mixin [{$mixin}] class bind of [{$bind}] not found.");
        throw_if(! method_exists($bind, 'mixin'), "Mixin [{$mixin}] class bind of [{$bind}] does not have a mixin method.");

        $bind::mixin(new $mixin, (bool) config('ntrn.mixins.replace', true));
        $this->config('set', $cnfKey, true);
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
            \App\Prompts\FlowingOutput::class => \App\Prompts\Themes\Ntrn\FlowingOutputRenderer::class,
        ];

        \App\Prompts\Prompt::addTheme('ntrn', array_merge($ntrn, $additional));

        \App\Prompts\Prompt::theme('ntrn');
    }

    protected static function initValidatorExtensions(): void
    {
        Validator::replacer('distinct_with', function ($message, $attribute, $rule, $parameters, \Illuminate\Validation\Validator $validator) {
            $value = [$validator->getValue($attribute)];
            foreach ($parameters as $param) {
                $value[] = $validator->getValue($param);
            }
            $value = Arr::join($value, ' + ');
            $params = '['.Arr::join($parameters, ', ', ' and ').'] field'.(count($parameters) > 1 ? 's' : '');

            return "The {$attribute} field must consist a distinct combination with {$params}. Combination of [{$value}] already exists.";
        });

        Validator::extendDependent('distinct_with', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {
            if (! isset($validator->customValues['ruleDistinctWith'])) {
                $validator->customValues['ruleDistinctWith'] = [];
            }

            $entry = [$value];
            foreach ($parameters as $param) {
                $entry[] = $validator->getValue($param);
            }

            if (in_array($entry, $validator->customValues['ruleDistinctWith'])) {
                return false;
            }

            $validator->customValues['ruleDistinctWith'][] = $entry;

            return true;
        });

        Validator::replacer('file_exists', function ($message, $attribute, $rule, $parameters, \Illuminate\Validation\Validator $validator) {
            return "The {$attribute} file does not exist.";
        });

        Validator::extend('file_exists', function ($attribute, $value, $parameters, \Illuminate\Validation\Validator $validator) {
            return File::exists($value);
        });
    }
}
