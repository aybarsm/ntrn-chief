<?php

namespace PhpSchool\Terminal;










class NonCanonicalReader
{



private $terminal;




private $wasCanonicalModeEnabled;







private $mappings = [];

public function __construct(Terminal $terminal)
{
$this->terminal = $terminal;
$this->wasCanonicalModeEnabled = $terminal->isCanonicalMode();
$this->terminal->disableCanonicalMode();
}

public function addControlMapping(string $character, string $mapToControl) : void
{
if (!InputCharacter::controlExists($mapToControl)) {
throw new \InvalidArgumentException(sprintf('Control "%s" does not exist', $mapToControl));
}

$this->mappings[$character] = $mapToControl;
}

public function addControlMappings(array $mappings) : void
{
foreach ($mappings as $character => $mapToControl) {
$this->addControlMapping($character, $mapToControl);
}
}






public function readCharacter() : InputCharacter
{
$char = $this->terminal->read(4);

if (isset($this->mappings[$char])) {
return InputCharacter::fromControlName($this->mappings[$char]);
}

return new InputCharacter($char);
}

public function __destruct()
{
if ($this->wasCanonicalModeEnabled) {
$this->terminal->enableCanonicalMode();
}
}
}
