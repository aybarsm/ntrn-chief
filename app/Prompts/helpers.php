<?php

namespace App\Prompts;

use Closure;

if (! function_exists('\App\Prompts\spin')) {
    function spin(string $message = '', ?Closure $callback = null): mixed
    {
        $spin = new \App\Prompts\Spinner($message);

        if ($callback !== null) {
            return $spin->spin($callback);
        }

        return $spin;
    }
}

if (! function_exists('\App\Prompts\progress')) {
    function progress(string $label, iterable|int $steps = 0, ?Closure $callback = null, string $hint = ''): array|Progress
    {
        $progress = new \App\Prompts\Progress($label, $steps, $hint);

        if ($callback !== null) {
            return $progress->map($callback);
        }

        return $progress;
    }
}
