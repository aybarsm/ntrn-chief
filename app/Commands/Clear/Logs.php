<?php

declare(strict_types=1);

namespace App\Commands\Clear;

use App\Framework\Commands\Command;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\File;

class Logs extends Command
{
    protected $signature = 'clear:logs';

    protected $description = 'Clear logs';

    public function handle(
        #[Config('logging.channels.single.path', '')] ?string $path,
    ): void {
        if (blank($path)) {
            $this->warn('Logs path is not defined');
        } elseif (! File::exists($path)) {
            $this->warn("Log file [{$path}] does not exist");
        } elseif (! File::isWritable($path)) {
            $this->warn("Log file [{$path}] is not writable");
        } else {
            $this->clearLogs($path);
        }
    }

    protected function clearLogs(string $path): void
    {
        File::put($path, '');
        $this->info('Logs cleared');
    }
}
