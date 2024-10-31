<?php

namespace App\Prompts;

use Laravel\Prompts\Prompt as LaravelPrompt;
use Symfony\Component\Console\Cursor;

abstract class Prompt extends LaravelPrompt
{
    use Concerns\Cursor;
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

    protected function render(): void
    {
        if (! $this->isRendered){
            $this->cursorPos = static::$cursor->getCurrentPosition();
            $this->isRendered = true;
        }

        parent::render();
    }

    public function clear(): void
    {
        if (! $this->isRendered){
            return;
        }

        static::$cursor->moveToPosition($this->cursorPos[0], $this->cursorPos[1]-1);
        static::$cursor->clearOutput();
    }

    /**
     * @inheritDoc
     */
    abstract public function value(): mixed;
}
