<?php

namespace App\Prompts\Concerns;

trait Cursor
{
    protected static bool $cursorHidden = false;

    public function hideCursor(): void
    {
        if (! static::$cursorHidden) {
            static::$cursor->hide();
        }

        static::$cursorHidden = true;
    }

    public function showCursor(): void
    {
        if (static::$cursorHidden) {
            static::$cursor->show();
        }
    }

    public function restoreCursor(): void
    {
        $this->showCursor();
    }

    public function moveCursor(int $x, int $y = 0): void
    {
        static::$cursor->moveToPosition($x, $y);
    }

    public function moveCursorToColumn(int $column): void
    {
        static::$cursor->moveToColumn($column);
    }

    public function moveCursorUp(int $lines): void
    {
        static::$cursor->moveUp($lines);
    }
}
