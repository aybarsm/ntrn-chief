<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'route:clear')]
class RouteClearCommand extends Command
{





protected $name = 'route:clear';






protected $description = 'Remove the route cache file';






protected $files;







public function __construct(Filesystem $files)
{
parent::__construct();

$this->files = $files;
}






public function handle()
{
$this->files->delete($this->laravel->getCachedRoutesPath());

$this->components->info('Route cache cleared successfully.');
}
}
