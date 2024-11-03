<?php

namespace App\Prompts;

use App\Services\Helper;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

if (! function_exists('\App\Prompts\text')) {
    function text(...$params): string
    {
        return (new TextPrompt(...$params))->prompt();
    }
}

if (! function_exists('\App\Prompts\textarea')) {
    function textarea(...$params): string
    {
        return (new TextareaPrompt(...$params))->prompt();
    }
}

if (! function_exists('\App\Prompts\password')) {
    function password(...$params): string
    {
        return (new PasswordPrompt(...$params))->prompt();
    }
}

if (! function_exists('\App\Prompts\select')) {
    function select(...$params): int|string
    {
        return (new SelectPrompt(...$params))->prompt();
    }
}

if (! function_exists('\App\Prompts\multiselect')) {
    function multiselect(...$params): array
    {
        return (new MultiSelectPrompt(...$params))->prompt();
    }
}

if (! function_exists('\App\Prompts\confirm')) {
    function confirm(...$params): bool
    {
        return (new ConfirmPrompt(...$params))->prompt();
    }
}

if (! function_exists('\App\Prompts\pause')) {
    function pause(...$params): bool
    {
        return (new PausePrompt(...$params))->prompt();
    }
}

if (! function_exists('\App\Prompts\clear')) {
    function clear(): void
    {
        (new Clear)->display();
    }
}

if (! function_exists('\App\Prompts\suggest')) {
    function suggest(...$params): string
    {
        return (new SuggestPrompt(...$params))->prompt();
    }
}

if (! function_exists('\App\Prompts\search')) {
    function search(...$params): int|string
    {
        return (new SearchPrompt(...$params))->prompt();
    }
}

if (! function_exists('\App\Prompts\multisearch')) {
    function multisearch(...$params): array
    {
        return (new MultiSearchPrompt(...$params))->prompt();
    }
}

if (! function_exists('\App\Prompts\spin')) {
    function spin(...$params): mixed
    {
        $spinner = new Spinner(...$params);

        $cbKey = Arr::has($params, 'callback') ? 'callback' : 1;
        if (isset($params[$cbKey]) && is_callable($params[$cbKey])) {
            return $spinner->spin($params[$cbKey]);
        }

        return $spinner;
    }
}

if (! function_exists('\App\Prompts\note')) {
    function note(...$params): void
    {
        (new Note(...$params))->display();
    }
}

if (! function_exists('\App\Prompts\error')) {
    function error(...$params): void
    {
        $params = array_merge($params, ['type' => 'error']);
        (new Note(...$params))->display();
    }
}

if (! function_exists('\App\Prompts\warning')) {
    function warning(...$params): void
    {
        $params = array_merge($params, ['type' => 'warning']);
        (new Note(...$params))->display();
    }
}

if (! function_exists('\App\Prompts\alert')) {
    function alert(...$params): void
    {
        $params = array_merge($params, ['type' => 'alert']);
        (new Note(...$params))->display();
    }
}

if (! function_exists('\App\Prompts\info')) {
    function info(...$params): void
    {
        $params = array_merge($params, ['type' => 'info']);
        (new Note(...$params))->display();
    }
}

if (! function_exists('\App\Prompts\intro')) {
    function intro(...$params): void
    {
        $params = array_merge($params, ['type' => 'intro']);
        (new Note(...$params))->display();
    }
}

if (! function_exists('\App\Prompts\outro')) {
    function outro(...$params): void
    {
        $params = array_merge($params, ['type' => 'outro']);
        (new Note(...$params))->display();
    }
}

if (! function_exists('\App\Prompts\table')) {
    function table(...$params): void
    {
        (new Table(...$params))->display();
    }
}

if (! function_exists('\App\Prompts\progress')) {
    function progress(...$params): array|Progress
    {
        $progress = new Progress(...$params);

        $cbKey = Arr::has($params, 'steps') ? 'steps' : Helper::getMethodParameterPosition(Progress::class, '__construct', 'steps');

        if (isset($params[$cbKey]) && is_callable($params[$cbKey])) {
            return $progress->map($params[$cbKey]);
        }

        return $progress;
    }
}

if (! function_exists('\App\Prompts\form')) {
    function form(): FormBuilder
    {
        return new FormBuilder;
    }
}
