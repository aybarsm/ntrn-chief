<?php

namespace App\Prompts;

use App\Traits\ConfigableOpen;
use Illuminate\Process\PendingProcess;
use Laravel\Prompts\Concerns\Scrolling;
use Laravel\Prompts\Concerns\Truncation;
use Laravel\Prompts\Concerns\TypedValue;
use Laravel\Prompts\Support\Utils;

class FlowingOutput extends Prompt
{
    use Scrolling, Truncation, TypedValue;
    public int $width = 120;
    public string $outputBody = '';

    protected bool $originalAsync;

    public function __construct(
        public string $label = '',
        int $rows = 5,
        public string $hint = '',
    ) {
        $this->scroll = $rows;

        $this->initializeScrolling();
        $this->trackTypedValue(
            default: '',
            submit: false,
            allowNewLine: true,
        );
    }

    public function wrappedValue(): string
    {
        return $this->mbWordwrap($this->value(), $this->width, PHP_EOL, true);
    }

    public function lines(): array
    {
        return explode(PHP_EOL, $this->wrappedValue());
    }

    protected function currentLineIndex(): int
    {
        $totalLineLength = 0;

        return (int) Utils::search($this->lines(), function ($line) use (&$totalLineLength) {
            $totalLineLength += mb_strlen($line) + 1;

            return $totalLineLength > $this->cursorPosition;
        }) ?: 0;
    }

    protected function adjustVisibleWindow(): void
    {
        if (count($this->lines()) < $this->scroll) {
            return;
        }

        $currentLineIndex = $this->currentLineIndex();

        while ($this->firstVisible + $this->scroll <= $currentLineIndex) {
            $this->firstVisible++;
        }

        if ($currentLineIndex === $this->firstVisible - 1) {
            $this->firstVisible = max(0, $this->firstVisible - 1);
        }

        // Make sure there are always the scroll amount visible
        if ($this->firstVisible + $this->scroll > count($this->lines())) {
            $this->firstVisible = count($this->lines()) - $this->scroll;
        }
    }

    protected function cursorOffset(): int
    {
        $cursorOffset = 0;

        preg_match_all('/\S{'.$this->width.',}/u', $this->value(), $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match) {
            if ($this->cursorPosition + $cursorOffset >= $match[1] + mb_strwidth($match[0])) {
                $cursorOffset += (int) floor(mb_strwidth($match[0]) / $this->width);
            }
        }

        return $cursorOffset;
    }

    public function valueWithCursor(): string
    {
        return $this->addCursor($this->wrappedValue(), $this->cursorPosition + $this->cursorOffset(), -1);
    }

    public function visible(): array
    {
        $this->adjustVisibleWindow();

        $withCursor = $this->valueWithCursor();

        return array_slice(explode(PHP_EOL, $withCursor), $this->firstVisible, $this->scroll, preserve_keys: true);
    }


    public function start(): void
    {
        $this->capturePreviousNewLines();

        if (function_exists('pcntl_signal')) {
            $this->originalAsync = pcntl_async_signals(true);
            pcntl_signal(SIGINT, function () {
                $this->state = 'cancel';
                $this->render();
                exit();
            });
        }

        $this->state = 'active';
        $this->hideCursor();
        $this->render();
    }

    public function finish(): void
    {
        $this->state = 'submit';
        $this->render();
        $this->restoreCursor();
        $this->resetSignals();
    }

    protected function resetSignals(): void
    {
        if (isset($this->originalAsync)) {
            pcntl_async_signals($this->originalAsync);
            pcntl_signal(SIGINT, SIG_DFL);
        }
    }

    public function addOutput(string $output): void
    {
        if (blank($output)){
            return;
        }
        $this->outputBody .= (! blank($this->outputBody) ? PHP_EOL : '') . trim($output);
        $this->handleDownKey();
        $this->render();
    }

    protected function handleDownKey(): void
    {
        $lines = $this->lines();

        // Line length + 1 for the newline character
        $lineLengths = array_map(fn ($line, $index) => mb_strlen($line) + ($index === count($lines) - 1 ? 0 : 1), $lines, range(0, count($lines) - 1));

        $currentLineIndex = $this->currentLineIndex();

        if ($currentLineIndex === count($lines) - 1) {
            // They're already at the last line, jump them to the last position
            $this->cursorPosition = mb_strlen(implode(PHP_EOL, $lines));

            return;
        }

        // Lines up to and including the current line
        $currentLines = array_slice($lineLengths, 0, $currentLineIndex + 1);

        $currentColumn = Utils::last($currentLines) - (array_sum($currentLines) - $this->cursorPosition);

        $destinationLineLength = $lineLengths[$currentLineIndex + 1] ?? Utils::last($currentLines);

        if ($currentLineIndex + 1 !== count($lines) - 1) {
            $destinationLineLength--;
        }

        $newColumn = min(max(0, $destinationLineLength), $currentColumn);

        $this->cursorPosition = array_sum($currentLines) + $newColumn;
    }

    public function prompt(): never
    {
        throw new \RuntimeException('Process Output cannot be prompted.');
    }

    public function value(): string
    {
        return $this->outputBody;
    }
}
