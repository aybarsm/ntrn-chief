<?php

namespace App\Traits\Prompt;

use App\Traits\Configable;
use Illuminate\Support\Str;

trait Statable
{
    use Configable;

    protected string $statesPath = 'states';

    protected function normaliseStatePath(string $path): string
    {
        $normalised = Str::of($path)->trim()->trim('.')->start("{$this->statesPath}.")->value();

        throw_if($normalised == "{$this->statesPath}.", new \InvalidArgumentException("Invalid [{$path}] state path."));

        return $normalised;
    }

    public function getState(string $path, mixed $default = null, bool $searchDefault = true): mixed
    {
        $path = $this->normaliseStatePath($path);

        if ($searchDefault){
            $defaultPath = '/(?<=^' . preg_quote($this->statesPath, '/') . ')[^.]+/';
            $defaultKey = Str::replaceMatches($defaultPath, 'default', $path, 1);
        }

        return $this->config('get', $path, ($searchDefault ? $this->config('get', $defaultKey, $default) : $default));
    }

    public function setState(string $path, mixed $value): static
    {
        $path = $this->normaliseStatePath($path);

        $this->config('set', $path, $value);

        return $this;
    }

    public function getStates(mixed $default): mixed
    {
        return $this->config('get', $this->statesPath, $default);
    }

    public function setStates(array $states): static
    {
        foreach ($states as $path => $value) {
            $this->setState($path, $value);
        }

        return $this;
    }
}
