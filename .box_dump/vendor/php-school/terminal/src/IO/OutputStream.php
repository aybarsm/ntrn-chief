<?php

namespace PhpSchool\Terminal\IO;




interface OutputStream
{



public function write(string $buffer) : void;




public function isInteractive() : bool;
}
