<?php

namespace Illuminate\Log\Context;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\ServiceProvider;

class ContextServiceProvider extends ServiceProvider
{





public function register()
{
$this->app->scoped(Repository::class);
}






public function boot()
{
Queue::createPayloadUsing(function ($connection, $queue, $payload) {
/**
@phpstan-ignore */
$context = Context::dehydrate();

return $context === null ? $payload : [
...$payload,
'illuminate:log:context' => $context,
];
});

$this->app['events']->listen(function (JobProcessing $event) {
/**
@phpstan-ignore */
Context::hydrate($event->job->payload()['illuminate:log:context'] ?? null);
});
}
}
