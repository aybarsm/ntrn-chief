<?php

namespace App\Providers;

use App\Contracts\Services\Console\IndicatorContract;
use App\Attributes\Console\TaskingCommand;
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
        $this->app->whenHasAttribute('TaskingCommad', function (...$params) {
            dump('TaskingCommand::whenHasAttribute');
        });
//        $this->app->bind(TaskingCommandContract::class, TaskingCommand::class);
//
//        $this->app->resolving(TaskingCommandContract::class, function (...$params) {
//            dump('TaskingCommandContract::resolving');
//            dump($params);
//        });
//
//        $this->app->resolving(CommandTaskList::class, function ($attribute, $app) {
//            dump('CommandTaskList::resolving');
//        });

//        $this->app->resolving(function ($object, $app) {
//            $reflectionClass = new \ReflectionClass($object);
//            foreach ($reflectionClass->getMethods() as $method) {
//                foreach ($method->getParameters() as $parameter) {
//                    if ($this->hasCommandTaskListAttribute($parameter)) {
//                        // Inject the calling instance, e.g., $object, as needed
//                        // You can use Laravel's binding capabilities to achieve this
//                    }
//                }
//            }
//        });


//        $this->app->whenHasAttribute(Commandtask::class, function (...$params){
//            dump('asdasds');
//            dump($params);
//        });
    }

//    protected function hasCommandTaskListAttribute(ReflectionParameter $parameter): bool
//    {
//        foreach ($parameter->getAttributes(CommandTaskList::class) as $attribute) {
//            return true;
//        }
//
//        return false;
//    }

    public function boot(): void
    {

    }

    protected function registerMixins(): void
    {

    }
}
