<?php

namespace Illuminate\Foundation\Console;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

trait InteractsWithComposerPackages
{







protected function requireComposerPackages(string $composer, array $packages)
{
if ($composer !== 'global') {
$command = [$this->phpBinary(), $composer, 'require'];
}

$command = array_merge(
$command ?? ['composer', 'require'],
$packages,
);

return ! (new Process($command, $this->laravel->basePath(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
->setTimeout(null)
->run(function ($type, $output) {
$this->output->write($output);
});
}






protected function phpBinary()
{
return (new PhpExecutableFinder())->find(false) ?: 'php';
}
}
