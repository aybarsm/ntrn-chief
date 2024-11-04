<?php

namespace App\Prompts\Concerns;

use Illuminate\Support\Str;

trait Maker
{
    public static function make(string $name, ...$params): mixed
    {
        $name = Str::of($name)
            ->lower()
            ->replaceMatches('/[^A-Za-z0-9]/', '')
            ->chopEnd('prompt')
            ->value();

        return match ($name) {
            'clear' => new \App\Prompts\Clear,
            'confirm' => new \App\Prompts\ConfirmPrompt(...$params),
            'formbuilder' => new \App\Prompts\FormBuilder(...$params),
            'formstep' => new \App\Prompts\FormStep(...$params),
            'multisearch' => new \App\Prompts\MultiSearchPrompt(...$params),
            'multiselect' => new \App\Prompts\MultiSelectPrompt(...$params),
            'note' => new \App\Prompts\Note(...$params),
            'error' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'error']))),
            'warning' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'warning']))),
            'alert' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'alert']))),
            'info' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'info']))),
            'intro' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'intro']))),
            'outro' => new \App\Prompts\Note(...(array_merge($params, ['type' => 'outro']))),
            'password' => new \App\Prompts\PasswordPrompt(...$params),
            'pause' => new \App\Prompts\PausePrompt(...$params),
            'progress' => new \App\Prompts\Progress(...$params),
            'search' => new \App\Prompts\SearchPrompt(...$params),
            'select' => new \App\Prompts\SelectPrompt(...$params),
            'suggest' => new \App\Prompts\SuggestPrompt(...$params),
            'table' => new \App\Prompts\Table(...$params),
            'terminal' => new \App\Prompts\Terminal,
            'textarea' => new \App\Prompts\TextareaPrompt(...$params),
            'text' => new \App\Prompts\TextPrompt(...$params),
            default => throw new \InvalidArgumentException("Prompt [{$name}] not found."),
        };
    }
}
