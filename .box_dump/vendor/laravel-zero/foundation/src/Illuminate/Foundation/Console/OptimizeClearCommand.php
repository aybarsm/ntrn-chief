<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'optimize:clear')]
class OptimizeClearCommand extends Command
{





protected $name = 'optimize:clear';






protected $description = 'Remove the cached bootstrap files';






public function handle()
{
$this->components->info('Clearing cached bootstrap files.');

collect([
'cache' => fn () => $this->callSilent('cache:clear') == 0,
'compiled' => fn () => $this->callSilent('clear-compiled') == 0,
'config' => fn () => $this->callSilent('config:clear') == 0,
'events' => fn () => $this->callSilent('event:clear') == 0,
'route' => fn () => $this->callSilent('route:clear') == 0,
'views' => fn () => $this->callSilent('view:clear') == 0,
])->each(fn ($task, $description) => $this->components->task($description, $task));

$this->newLine();
}
}
