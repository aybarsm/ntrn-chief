<?php

namespace Illuminate\Console\Concerns;

use Closure;
use Illuminate\Contracts\Console\PromptsForMissingInput as PromptsForMissingInputContract;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\text;

trait PromptsForMissingInput
{







protected function interact(InputInterface $input, OutputInterface $output)
{
parent::interact($input, $output);

if ($this instanceof PromptsForMissingInputContract) {
$this->promptForMissingArguments($input, $output);
}
}








protected function promptForMissingArguments(InputInterface $input, OutputInterface $output)
{
$prompted = collect($this->getDefinition()->getArguments())
->reject(fn (InputArgument $argument) => $argument->getName() === 'command')
->filter(fn (InputArgument $argument) => $argument->isRequired() && match (true) {
$argument->isArray() => empty($input->getArgument($argument->getName())),
default => is_null($input->getArgument($argument->getName())),
})
->each(function (InputArgument $argument) use ($input) {
$label = $this->promptForMissingArgumentsUsing()[$argument->getName()] ??
'What is '.lcfirst($argument->getDescription() ?: ('the '.$argument->getName())).'?';

if ($label instanceof Closure) {
return $input->setArgument($argument->getName(), $argument->isArray() ? Arr::wrap($label()) : $label());
}

if (is_array($label)) {
[$label, $placeholder] = $label;
}

$answer = text(
label: $label,
placeholder: $placeholder ?? '',
validate: fn ($value) => empty($value) ? "The {$argument->getName()} is required." : null,
);

$input->setArgument($argument->getName(), $argument->isArray() ? [$answer] : $answer);
})
->isNotEmpty();

if ($prompted) {
$this->afterPromptingForMissingArguments($input, $output);
}
}






protected function promptForMissingArgumentsUsing()
{
return [];
}








protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
{

}







protected function didReceiveOptions(InputInterface $input)
{
return collect($this->getDefinition()->getOptions())
->reject(fn ($option) => $input->getOption($option->getName()) === $option->getDefault())
->isNotEmpty();
}
}
