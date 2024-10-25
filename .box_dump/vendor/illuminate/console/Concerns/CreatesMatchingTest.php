<?php

namespace Illuminate\Console\Concerns;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

trait CreatesMatchingTest
{





protected function addTestOptions()
{
foreach (['test' => 'Test', 'pest' => 'Pest', 'phpunit' => 'PHPUnit'] as $option => $name) {
$this->getDefinition()->addOption(new InputOption(
$option,
null,
InputOption::VALUE_NONE,
"Generate an accompanying {$name} test for the {$this->type}"
));
}
}







protected function handleTestCreation($path)
{
if (! $this->option('test') && ! $this->option('pest') && ! $this->option('phpunit')) {
return false;
}

return $this->call('make:test', [
'name' => Str::of($path)->after($this->laravel['path'])->beforeLast('.php')->append('Test')->replace('\\', '/'),
'--pest' => $this->option('pest'),
'--phpunit' => $this->option('phpunit'),
]) == 0;
}
}