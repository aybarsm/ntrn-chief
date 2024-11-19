<?php

namespace App\Traits\Commands;

use Illuminate\Support\Str;

trait SignalHandler
{
    private int $defaultSignal = 0;

    private array $signalHandlers = [];

    public function getDefaultSignal(): int
    {
        return $this->defaultSignal;
    }

    private function setDefaultSignal(int $signal): static
    {
        $this->defaultSignal = $signal;

        return $this;
    }

    protected function setSignalHandlers(array $handlers): static
    {
        foreach ($handlers as $signalName => $action) {
            $this->setSignalHandler($signalName, $action);
        }

        return $this;
    }

    protected function setSignalHandler(string $signalName, callable $action): static
    {
        throw_if(! Str::startsWith($signalName, 'SIG') && ! Str::startsWith($signalName, 'SIG_') && ! defined($signalName),
            \InvalidArgumentException::class, "Invalid signal name [{$signalName}]");

        $this->signalHandlers[constant($signalName)] = $action;

        return $this;
    }

    public function getSubscribedSignals(): array
    {
        return array_keys($this->signalHandlers);
    }

    public function handleSignal(int $signal, int|false $previousExitCode = 0): int|false
    {
        if (isset($this->signalHandlers[$signal])) {
            return $this->signalHandlers[$signal]($previousExitCode);
        }

        return $this->defaultSignal;
    }
}
