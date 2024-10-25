<?php










namespace Symfony\Component\Process\Pipes;

use Symfony\Component\Process\Process;








class UnixPipes extends AbstractPipes
{
private ?bool $ttyMode;
private bool $ptyMode;
private bool $haveReadSupport;

public function __construct(?bool $ttyMode, bool $ptyMode, mixed $input, bool $haveReadSupport)
{
$this->ttyMode = $ttyMode;
$this->ptyMode = $ptyMode;
$this->haveReadSupport = $haveReadSupport;

parent::__construct($input);
}

public function __sleep(): array
{
throw new \BadMethodCallException('Cannot serialize '.__CLASS__);
}

public function __wakeup(): void
{
throw new \BadMethodCallException('Cannot unserialize '.__CLASS__);
}

public function __destruct()
{
$this->close();
}

public function getDescriptors(): array
{
if (!$this->haveReadSupport) {
$nullstream = fopen('/dev/null', 'c');

return [
['pipe', 'r'],
$nullstream,
$nullstream,
];
}

if ($this->ttyMode) {
return [
['file', '/dev/tty', 'r'],
['file', '/dev/tty', 'w'],
['file', '/dev/tty', 'w'],
];
}

if ($this->ptyMode && Process::isPtySupported()) {
return [
['pty'],
['pty'],
['pty'],
];
}

return [
['pipe', 'r'],
['pipe', 'w'], 
['pipe', 'w'], 
];
}

public function getFiles(): array
{
return [];
}

public function readAndWrite(bool $blocking, bool $close = false): array
{
$this->unblock();
$w = $this->write();

$read = $e = [];
$r = $this->pipes;
unset($r[0]);


set_error_handler($this->handleError(...));
if (($r || $w) && false === stream_select($r, $w, $e, 0, $blocking ? Process::TIMEOUT_PRECISION * 1E6 : 0)) {
restore_error_handler();


if (!$this->hasSystemCallBeenInterrupted()) {
$this->pipes = [];
}

return $read;
}
restore_error_handler();

foreach ($r as $pipe) {


$read[$type = array_search($pipe, $this->pipes, true)] = '';

do {
$data = @fread($pipe, self::CHUNK_SIZE);
$read[$type] .= $data;
} while (isset($data[0]) && ($close || isset($data[self::CHUNK_SIZE - 1])));

if (!isset($read[$type][0])) {
unset($read[$type]);
}

if ($close && feof($pipe)) {
fclose($pipe);
unset($this->pipes[$type]);
}
}

return $read;
}

public function haveReadSupport(): bool
{
return $this->haveReadSupport;
}

public function areOpen(): bool
{
return (bool) $this->pipes;
}
}
