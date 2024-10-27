<?php

namespace App\Services\Console;

use Illuminate\Support\Facades\App;
use Laravel\Prompts\Progress;

class Indicator
{
    protected array|Progress $indicator;

    public function __construct(
        protected string $type,
        protected array $options
    )
    {
        $this->indicator = match ($type) {
            'progress:stream' => App::make(Progress::class, array_merge($options, ['steps' => -1])),
        };
    }

    public function update($total, $completed): void
    {
        if ($this->type == 'progress:stream') {
            if ($this->indicator->total === -1 && $total > 0) {
                $this->indicator->total = $total;
                $this->indicator->start();
            }

            if ($this->indicator->total > -1) {
                if ($this->indicator->progress < $completed) {
                    $this->indicator->advance((int)$completed - (int)$this->indicator->progress);
                }

                if ($this->indicator->progress === $total) {
                    $this->indicator->label('Download completed.');
                    $this->indicator->render();
                    $this->indicator->finish();
                }
            }
        }
    }

}
