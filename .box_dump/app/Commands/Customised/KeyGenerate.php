<?php

declare(strict_types=1);

namespace App\Commands\Customised;

use Illuminate\Foundation\Console\KeyGenerateCommand;

class KeyGenerate extends KeyGenerateCommand
{
protected $signature = 'key:generate
    {--s|show : Display the key instead of modifying files}
    {--f|force : Force the operation to run when in production}
    {--r|replace : Replace the application key when it is already set}';
protected $description = 'Set the application key (Customised)';

public function handle(): void
{
$key = $this->generateRandomKey();

if ($this->option('show')) {
$this->line($key);
return;
}

if (! blank($this->laravel['config']['app.key']) && ! $this->option('replace')) {
$this->error('Application key is already set. Use the (-r|--replace) option to force the operation.');
return;
}

if (! $this->setKeyInEnvironmentFile($key)) {
return;
}

$this->laravel['config']['app.key'] = $key;

$this->components->info('Application key set successfully.');
}
}
