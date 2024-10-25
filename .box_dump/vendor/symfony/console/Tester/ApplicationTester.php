<?php










namespace Symfony\Component\Console\Tester;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;











class ApplicationTester
{
use TesterTrait;

public function __construct(
private Application $application,
) {
}













public function run(array $input, array $options = []): int
{
$prevShellVerbosity = getenv('SHELL_VERBOSITY');

try {
$this->input = new ArrayInput($input);
if (isset($options['interactive'])) {
$this->input->setInteractive($options['interactive']);
}

if ($this->inputs) {
$this->input->setStream(self::createStream($this->inputs));
}

$this->initOutput($options);

return $this->statusCode = $this->application->run($this->input, $this->output);
} finally {


if (false === $prevShellVerbosity) {
if (\function_exists('putenv')) {
@putenv('SHELL_VERBOSITY');
}
unset($_ENV['SHELL_VERBOSITY']);
unset($_SERVER['SHELL_VERBOSITY']);
} else {
if (\function_exists('putenv')) {
@putenv('SHELL_VERBOSITY='.$prevShellVerbosity);
}
$_ENV['SHELL_VERBOSITY'] = $prevShellVerbosity;
$_SERVER['SHELL_VERBOSITY'] = $prevShellVerbosity;
}
}
}
}
