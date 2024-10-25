<?php

namespace Illuminate\Support\Process;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder as SymfonyPhpExecutableFinder;

class PhpExecutableFinder extends SymfonyPhpExecutableFinder
{



#[\Override]
public function find(bool $includeArgs = true): string|false
{
if ($herdPath = getenv('HERD_HOME')) {
return (new ExecutableFinder)->find('php', false, [implode(DIRECTORY_SEPARATOR, [$herdPath, 'bin'])]);
}

return parent::find($includeArgs);
}
}
