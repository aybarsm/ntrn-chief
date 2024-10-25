<?php

namespace Illuminate\Process;

use Symfony\Component\Process\Process;

class FakeProcessDescription
{





public $processId = 1000;






public $output = [];






public $exitCode = 0;






public $runIterations = 0;







public function id(int $processId)
{
$this->processId = $processId;

return $this;
}







public function output(array|string $output)
{
if (is_array($output)) {
collect($output)->each(fn ($line) => $this->output($line));

return $this;
}

$this->output[] = ['type' => 'out', 'buffer' => rtrim($output, "\n")."\n"];

return $this;
}







public function errorOutput(array|string $output)
{
if (is_array($output)) {
collect($output)->each(fn ($line) => $this->errorOutput($line));

return $this;
}

$this->output[] = ['type' => 'err', 'buffer' => rtrim($output, "\n")."\n"];

return $this;
}







public function replaceOutput(string $output)
{
$this->output = collect($this->output)->reject(function ($output) {
return $output['type'] === 'out';
})->values()->all();

if (strlen($output) > 0) {
$this->output[] = [
'type' => 'out',
'buffer' => rtrim($output, "\n")."\n",
];
}

return $this;
}







public function replaceErrorOutput(string $output)
{
$this->output = collect($this->output)->reject(function ($output) {
return $output['type'] === 'err';
})->values()->all();

if (strlen($output) > 0) {
$this->output[] = [
'type' => 'err',
'buffer' => rtrim($output, "\n")."\n",
];
}

return $this;
}







public function exitCode(int $exitCode)
{
$this->exitCode = $exitCode;

return $this;
}







public function iterations(int $iterations)
{
return $this->runsFor(iterations: $iterations);
}







public function runsFor(int $iterations)
{
$this->runIterations = $iterations;

return $this;
}







public function toSymfonyProcess(string $command)
{
return Process::fromShellCommandline($command);
}







public function toProcessResult(string $command)
{
return new FakeProcessResult(
command: $command,
exitCode: $this->exitCode,
output: $this->resolveOutput(),
errorOutput: $this->resolveErrorOutput(),
);
}






protected function resolveOutput()
{
$output = collect($this->output)
->filter(fn ($output) => $output['type'] === 'out');

return $output->isNotEmpty()
? rtrim($output->map->buffer->implode(''), "\n")."\n"
: '';
}






protected function resolveErrorOutput()
{
$output = collect($this->output)
->filter(fn ($output) => $output['type'] === 'err');

return $output->isNotEmpty()
? rtrim($output->map->buffer->implode(''), "\n")."\n"
: '';
}
}
