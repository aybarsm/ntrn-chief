<?php










namespace Symfony\Component\Console\Completion\Output;

use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Output\OutputInterface;




class FishCompletionOutput implements CompletionOutputInterface
{
public function write(CompletionSuggestions $suggestions, OutputInterface $output): void
{
$values = [];
foreach ($suggestions->getValueSuggestions() as $value) {
$values[] = $value->getValue().($value->getDescription() ? "\t".$value->getDescription() : '');
}
foreach ($suggestions->getOptionSuggestions() as $option) {
$values[] = '--'.$option->getName().($option->getDescription() ? "\t".$option->getDescription() : '');
if ($option->isNegatable()) {
$values[] = '--no-'.$option->getName().($option->getDescription() ? "\t".$option->getDescription() : '');
}
}
$output->write(implode("\n", $values));
}
}