<?php

namespace App\Prompts;

use App\Traits\ConfigableOpen;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Laravel\Prompts\Prompt as LaravelPrompt;
use Symfony\Component\Console\Cursor;

abstract class Prompt extends LaravelPrompt implements Contracts\PromptContract
{
    use Concerns\Cursor, Concerns\Maker, Concerns\ThemesExtended, Conditionable, ConfigableOpen, Macroable;

    protected static Cursor $cursor;

    protected array $cursorPos;

    protected bool $isRendered = false;

    protected static function cursor(): Cursor
    {
        return self::$cursor ??= new Cursor(parent::output());
    }

    public static function setCursor(Cursor $cursor): void
    {
        self::$cursor = $cursor;
    }

    protected function setCursorPos(): void
    {
        $this->cursorPos = static::$cursor->getCurrentPosition();
    }

    protected function render(): void
    {
        if (! $this->isRendered) {
            $this->setCursorPos();
            $this->isRendered = true;
        }

        parent::render();
    }

    public function clear(): void
    {
        if (! $this->isRendered) {
            return;
        }

        static::$cursor->moveToPosition($this->cursorPos[0], $this->cursorPos[1] - 1);
        static::$cursor->clearOutput();
    }

    public function moveCursorOld(int $x, int $y = 0): void
    {
        $sequence = '';

        if ($x < 0) {
            $sequence .= "\e[".abs($x).'D'; // Left
        } elseif ($x > 0) {
            $sequence .= "\e[{$x}C"; // Right
        }

        if ($y < 0) {
            $sequence .= "\e[".abs($y).'A'; // Up
        } elseif ($y > 0) {
            $sequence .= "\e[{$y}B"; // Down
        }

        static::writeDirectly($sequence);
    }

    public function eraseRenderedLines(): void
    {
        $lines = Str::lines($this->prevFrame)->count();
        $this->moveCursorOld(-999, -$lines + 1);
        $this->eraseDown();
    }

    public function prompt(): mixed
    {
        if (! $this->isRendered) {
            $this->setCursorPos();
            $this->isRendered = true;
        }

        return parent::prompt();
    }

    /**
     * {@inheritDoc}
     */
    abstract public function value(): mixed;
}
