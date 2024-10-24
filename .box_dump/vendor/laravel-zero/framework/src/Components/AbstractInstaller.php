<?php

declare(strict_types=1);










namespace LaravelZero\Framework\Components;

use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;
use LaravelZero\Framework\Contracts\Commands\Component\InstallerContract;
use LaravelZero\Framework\Contracts\Providers\ComposerContract;




abstract class AbstractInstaller extends Command implements InstallerContract
{
protected Filesystem $files;

protected ComposerContract $composer;

public function __construct(Filesystem $files, ComposerContract $composer)
{
parent::__construct();

$this->files = $files;

$this->composer = $composer;
}


public function handle()
{
$this->install();
}

protected function require(string $package, bool $dev = false): InstallerContract
{
$this->task(
'Require package via Composer',
function () use ($package, $dev) {
return $this->composer->require($package, $dev);
}
);

return $this;
}

protected function remove(string $package, bool $dev = false): InstallerContract
{
$this->task(
'Remove package via Composer',
function () use ($package, $dev) {
return $this->composer->remove($package, $dev);
}
);

return $this;
}
}
