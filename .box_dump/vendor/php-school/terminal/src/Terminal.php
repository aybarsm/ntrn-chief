<?php

namespace PhpSchool\Terminal;





interface Terminal
{



public function getWidth() : int;




public function getHeight() : int;




public function getColourSupport() : int;





public function disableEchoBack() : void;




public function enableEchoBack() : void;




public function isEchoBack() : bool;






public function disableCanonicalMode() : void;






public function enableCanonicalMode() : void;




public function isCanonicalMode() : bool;







public function isInteractive() : bool;




public function restoreOriginalConfiguration() : void;




public function supportsColour() : bool;




public function clear() : void;




public function clearLine() : void;




public function clearDown() : void;




public function clean() : void;




public function enableCursor() : void;




public function disableCursor() : void;




public function moveCursorToTop() : void;




public function moveCursorToRow(int $rowNumber) : void;




public function moveCursorToColumn(int $columnNumber) : void;




public function read(int $bytes) : string;




public function write(string $buffer) : void;
}
