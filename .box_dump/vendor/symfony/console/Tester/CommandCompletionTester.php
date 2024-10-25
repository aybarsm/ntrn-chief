<?php










namespace Symfony\Component\Console\Tester;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;






class CommandCompletionTester
{
public function __construct(
private Command $command,
) {
}




public function complete(array $input): array
{
$currentIndex = \count($input);
if ('' === end($input)) {
array_pop($input);
}
array_unshift($input, $this->command->getName());

$completionInput = CompletionInput::fromTokens($input, $currentIndex);
$completionInput->bind($this->command->getDefinition());
$suggestions = new CompletionSuggestions();

$this->command->complete($completionInput, $suggestions);

$options = [];
foreach ($suggestions->getOptionSuggestions() as $option) {
$options[] = '--'.$option->getName();
}

return array_map('strval', array_merge($options, $suggestions->getValueSuggestions()));
}
}
