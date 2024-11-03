<?php

namespace App\Prompts\Themes\Ntrn;

use Illuminate\Support\Str;
use Laravel\Prompts\Output\BufferedConsoleOutput;
use App\Prompts\Table;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Helper\TableStyle;

class TableRenderer extends Renderer
{
    /**
     * Render the table.
     */
    public function __invoke(Table $table): string
    {
        $tableStyle = (new TableStyle)
            ->setHorizontalBorderChars('─')
            ->setVerticalBorderChars('│', '│')
            ->setCellHeaderFormat($this->dim('<fg=default>%s</>'))
            ->setCellRowFormat('<fg=default>%s</>');

        if (empty($table->headers)) {
            $tableStyle->setCrossingChars('┼', '', '', '', '┤', '┘</>', '┴', '└', '├', '<fg=gray>┌', '┬', '┐');
        } else {
            $tableStyle->setCrossingChars('┼', '<fg=gray>┌', '┬', '┐', '┤', '┘</>', '┴', '└', '├');
        }

        $buffered = new BufferedConsoleOutput;

        $symfonyTable = (new SymfonyTable($buffered))
            ->setHeaders($table->headers)
            ->setRows($table->rows)
            ->setStyle($tableStyle);

        foreach(['HeaderTitle', 'FooterTitle'] as $setting)
        {
            $cnf = Str::of($setting)->kebab()->replace('-', '.')->value();
            if (! blank($table->config('get', $cnf)))
            {
                $method = "set{$setting}";
                $symfonyTable->{$method}($table->config('get', $cnf));
            }
        }

        $symfonyTable->render();

        foreach (explode(PHP_EOL, trim($buffered->content(), PHP_EOL)) as $line) {
            $this->line(' '.$line);
        }

        return $this;
    }
}
