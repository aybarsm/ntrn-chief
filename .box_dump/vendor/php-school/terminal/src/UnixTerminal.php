<?php

namespace PhpSchool\Terminal;

use PhpSchool\Terminal\Exception\NotInteractiveTerminal;
use PhpSchool\Terminal\IO\InputStream;
use PhpSchool\Terminal\IO\OutputStream;





class UnixTerminal implements Terminal
{



private $isCanonical;







private $echoBack = true;




private $width;




private $height;




private $colourSupport;




private $originalConfiguration;




private $input;




private $output;

public function __construct(InputStream $input, OutputStream $output)
{
$this->getOriginalConfiguration();
$this->getOriginalCanonicalMode();
$this->input = $input;
$this->output = $output;
}

private function getOriginalCanonicalMode() : void
{
exec('stty -a', $output);
$this->isCanonical = (strpos(implode("\n", $output), ' icanon') !== false);
}

public function getWidth() : int
{
return $this->width ?: $this->width = (int) exec('tput cols');
}

public function getHeight() : int
{
return $this->height ?: $this->height = (int) exec('tput lines');
}

public function getColourSupport() : int
{
return $this->colourSupport ?: $this->colourSupport = (int) exec('tput colors');
}

private function getOriginalConfiguration() : string
{
return $this->originalConfiguration ?: $this->originalConfiguration = exec('stty -g');
}





public function disableEchoBack() : void
{
exec('stty -echo');
$this->echoBack = false;
}




public function enableEchoBack() : void
{
exec('stty echo');
$this->echoBack = true;
}




public function isEchoBack() : bool
{
return $this->echoBack;
}






public function disableCanonicalMode() : void
{
if ($this->isCanonical) {
exec('stty -icanon');
$this->isCanonical = false;
}
}






public function enableCanonicalMode() : void
{
if (!$this->isCanonical) {
exec('stty icanon');
$this->isCanonical = true;
}
}




public function isCanonicalMode() : bool
{
return $this->isCanonical;
}




public function restoreOriginalConfiguration() : void
{
exec('stty ' . $this->getOriginalConfiguration());
}







public function isInteractive() : bool
{
return $this->input->isInteractive() && $this->output->isInteractive();
}





public function mustBeInteractive() : void
{
if (!$this->input->isInteractive()) {
throw NotInteractiveTerminal::inputNotInteractive();
}

if (!$this->output->isInteractive()) {
throw NotInteractiveTerminal::outputNotInteractive();
}
}




public function supportsColour() : bool
{
if (DIRECTORY_SEPARATOR === '\\') {
return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI') || 'xterm' === getenv('TERM');
}

return $this->isInteractive();
}

public function clear() : void
{
$this->output->write("\033[2J");
}

public function clearLine() : void
{
$this->output->write("\033[2K");
}




public function clearDown() : void
{
$this->output->write("\033[J");
}

public function clean() : void
{
foreach (range(0, $this->getHeight()) as $rowNum) {
$this->moveCursorToRow($rowNum);
$this->clearLine();
}
}

public function enableCursor() : void
{
$this->output->write("\033[?25h");
}

public function disableCursor() : void
{
$this->output->write("\033[?25l");
}

public function moveCursorToTop() : void
{
$this->output->write("\033[H");
}

public function moveCursorToRow(int $rowNumber) : void
{
$this->output->write(sprintf("\033[%d;0H", $rowNumber));
}

public function moveCursorToColumn(int $column) : void
{
$this->output->write(sprintf("\033[%dC", $column));
}

public function showSecondaryScreen() : void
{
$this->output->write("\033[?47h");
}

public function showPrimaryScreen() : void
{
$this->output->write("\033[?47l");
}




public function read(int $bytes): string
{
$buffer = '';
$this->input->read($bytes, function ($data) use (&$buffer) {
$buffer .= $data;
});
return $buffer;
}




public function write(string $buffer): void
{
$this->output->write($buffer);
}




public function __destruct()
{
$this->restoreOriginalConfiguration();
}
}
