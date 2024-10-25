<?php

namespace PhpSchool\Terminal\IO;




interface InputStream
{




public function read(int $numBytes, callable $callback) : void;




public function isInteractive() : bool;
}
