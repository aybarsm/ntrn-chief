<?php

namespace Illuminate\Support;

use Illuminate\Support\Defer\DeferredCallback;
use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Process\PhpExecutableFinder;

if (! function_exists('Illuminate\Support\defer')) {








function defer(?callable $callback = null, ?string $name = null, bool $always = false)
{
if ($callback === null) {
return app(DeferredCallbackCollection::class);
}

return tap(
new DeferredCallback($callback, $name, $always),
fn ($deferred) => app(DeferredCallbackCollection::class)[] = $deferred
);
}
}

if (! function_exists('Illuminate\Support\enum_value')) {
/**
@template
@template








*/
function enum_value($value, $default = null)
{
return transform($value, fn ($value) => match (true) {
$value instanceof \BackedEnum => $value->value,
$value instanceof \UnitEnum => $value->name,

default => $value,
}, $default ?? $value);
}
}

if (! function_exists('Illuminate\Support\php_binary')) {





function php_binary()
{
return (new PhpExecutableFinder)->find(false) ?: 'php';
}
}
