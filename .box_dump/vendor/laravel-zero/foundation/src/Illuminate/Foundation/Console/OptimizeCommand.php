<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'optimize')]
class OptimizeCommand extends Command
{





protected $name = 'optimize';






protected $description = 'Cache framework bootstrap, configuration, and metadata to increase performance';






public function handle()
{
$this->components->info('Caching framework bootstrap, configuration, and metadata.');

collect([
'config' => fn () => $this->callSilent('config:cache') == 0,
'events' => fn () => $this->callSilent('event:cache') == 0,
'routes' => fn () => $this->callSilent('route:cache') == 0,
'views' => fn () => $this->callSilent('view:cache') == 0,
])->each(fn ($task, $description) => $this->components->task($description, $task));

$this->newLine();
}
}
