<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu;

use PhpSchool\CliMenu\Exception\CannotShrinkMenuException;
use PhpSchool\CliMenu\Terminal\TerminalFactory;
use PhpSchool\CliMenu\Util\ColourUtil;
use PhpSchool\CliMenu\Util\StringUtil as s;
use PhpSchool\Terminal\Terminal;
use Assert\Assertion;






class MenuStyle
{



protected $terminal;




protected $fg;




protected $bg;











protected $width;









private $requestedWidth;




protected $margin = 0;




protected $paddingTopBottom = 0;




protected $paddingLeftRight = 0;




private $paddingTopBottomRows = [];




protected $contentWidth;




private $itemExtra;




private $displaysExtra;




private $titleSeparator;




private $coloursSetCode;




private $invertedColoursSetCode = "\033[7m";




private $invertedColoursUnsetCode = "\033[27m";




private $coloursResetCode = "\033[0m";




private $borderTopWidth = 0;




private $borderRightWidth = 0;




private $borderBottomWidth = 0;




private $borderLeftWidth = 0;




private $borderColour = 'white';




private $borderTopRows = [];




private $borderBottomRows = [];




private $marginAuto = false;




private $debugMode = false;






private static $defaultStyleValues = [
'fg' => 'white',
'bg' => 'blue',
'width' => 100,
'paddingTopBottom' => 1,
'paddingLeftRight' => 2,
'margin' => 2,
'itemExtra' => '✔',
'displaysExtra' => false,
'titleSeparator' => '=',
'borderTopWidth' => 0,
'borderRightWidth' => 0,
'borderBottomWidth' => 0,
'borderLeftWidth' => 0,
'borderColour' => 'white',
'marginAuto' => false,
];




private static $availableForegroundColors = [
'black' => 30,
'red' => 31,
'green' => 32,
'yellow' => 33,
'blue' => 34,
'magenta' => 35,
'cyan' => 36,
'white' => 37,
'default' => 39,
];




private static $availableBackgroundColors = [
'black' => 40,
'red' => 41,
'green' => 42,
'yellow' => 43,
'blue' => 44,
'magenta' => 45,
'cyan' => 46,
'white' => 47,
'default' => 49,
];




private static $availableOptions = [
'bold' => ['set' => 1, 'unset' => 22],
'dim' => ['set' => 2, 'unset' => 22],
'underscore' => ['set' => 4, 'unset' => 24],
'blink' => ['set' => 5, 'unset' => 25],
'reverse' => ['set' => 7, 'unset' => 27],
'conceal' => ['set' => 8, 'unset' => 28]
];




public function __construct(Terminal $terminal = null)
{
$this->terminal = $terminal ?: TerminalFactory::fromSystem();

$this->fg = self::$defaultStyleValues['fg'];
$this->bg = self::$defaultStyleValues['bg'];

$this->generateColoursSetCode();

$this->setWidth(self::$defaultStyleValues['width']);
$this->setPaddingTopBottom(self::$defaultStyleValues['paddingTopBottom']);
$this->setPaddingLeftRight(self::$defaultStyleValues['paddingLeftRight']);
$this->setMargin(self::$defaultStyleValues['margin']);
$this->setItemExtra(self::$defaultStyleValues['itemExtra']);
$this->setDisplaysExtra(self::$defaultStyleValues['displaysExtra']);
$this->setTitleSeparator(self::$defaultStyleValues['titleSeparator']);
$this->setBorderTopWidth(self::$defaultStyleValues['borderTopWidth']);
$this->setBorderRightWidth(self::$defaultStyleValues['borderRightWidth']);
$this->setBorderBottomWidth(self::$defaultStyleValues['borderBottomWidth']);
$this->setBorderLeftWidth(self::$defaultStyleValues['borderLeftWidth']);
$this->setBorderColour(self::$defaultStyleValues['borderColour']);
}

public function hasChangedFromDefaults() : bool
{
$currentValues = [
$this->fg,
$this->bg,
$this->width,
$this->paddingTopBottom,
$this->paddingLeftRight,
$this->margin,
$this->itemExtra,
$this->displaysExtra,
$this->titleSeparator,
$this->borderTopWidth,
$this->borderRightWidth,
$this->borderBottomWidth,
$this->borderLeftWidth,
$this->borderColour,
$this->marginAuto,
];

$defaultStyleValues = self::$defaultStyleValues;
if ($this->width !== $this->requestedWidth) {
$defaultStyleValues['width'] = $this->width;
}

return $currentValues !== array_values($defaultStyleValues);
}









public function getDisabledItemText(string $text) : string
{
return sprintf(
"\033[%sm\033[%sm%s\033[%sm\033[%sm",
self::$availableOptions['dim']['set'],
$this->getForegroundColourCode(true),
$text,
$this->getForegroundColourCode(),
self::$availableOptions['dim']['unset']
);
}








private function getForegroundColourCode(bool $bright = false) : string
{
if (!ctype_digit($this->fg)) {
$fgCode = (int)self::$availableForegroundColors[$this->fg];
$fgCode += ($bright ? 60 : 0);
} else {
$fgCode = sprintf("38;5;%s", ((int)$this->fg + ($bright ? 60 : 0)));
}

return (string)$fgCode;
}








private function getBackgroundColourCode(bool $bright = false) : string
{
if (!ctype_digit($this->bg)) {
$bgCode = (int)self::$availableBackgroundColors[$this->bg];
$bgCode += ($bright ? 60 : 0);
} else {
$bgCode = sprintf("48;5;%s", ((int)$this->bg + ($bright ? 60 : 0)));
}

return (string)$bgCode;
}




private function generateColoursSetCode() : void
{
$this->coloursSetCode = sprintf(
"\033[%s;%sm",
$this->getForegroundColourCode(),
$this->getBackgroundColourCode()
);
}




public function getColoursSetCode() : string
{
return $this->coloursSetCode;
}




public function getInvertedColoursSetCode() : string
{
return $this->invertedColoursSetCode;
}




public function getInvertedColoursUnsetCode() : string
{
return $this->invertedColoursUnsetCode;
}




public function getColoursResetCode() : string
{
return $this->coloursResetCode;
}






protected function calculateContentWidth() : void
{
$this->contentWidth = $this->width
- ($this->paddingLeftRight * 2)
- ($this->borderRightWidth + $this->borderLeftWidth);

if ($this->contentWidth < 0) {
$this->contentWidth = 0;
}
}

public function getFg() : string
{
return $this->fg;
}

public function setFg(string $fg, string $fallback = null) : self
{
$this->fg = ColourUtil::validateColour(
$this->terminal,
$fg,
$fallback
);
$this->generateColoursSetCode();

return $this;
}

public function getBg() : string
{
return $this->bg;
}

public function setBg(string $bg, string $fallback = null) : self
{
$this->bg = ColourUtil::validateColour(
$this->terminal,
$bg,
$fallback
);

$this->generateColoursSetCode();
$this->generatePaddingTopBottomRows();

return $this;
}

public function getWidth() : int
{
return $this->width;
}

public function setWidth(int $width) : self
{
Assertion::greaterOrEqualThan($width, 0);

$this->requestedWidth = $width;

$this->width = $this->maybeShrinkWidth($this->marginAuto ? 0 : $this->margin, $width);

if ($this->marginAuto) {
$this->calculateMarginAuto($this->width);
}

$this->calculateContentWidth();
$this->generateBorderRows();
$this->generatePaddingTopBottomRows();

return $this;
}

private function maybeShrinkWidth(int $margin, int $width) : int
{
if ($width + ($margin * 2) >= $this->terminal->getWidth()) {
$width = $this->terminal->getWidth() - ($margin * 2);

if ($width <= 0) {
throw CannotShrinkMenuException::fromMarginAndTerminalWidth($margin, $this->terminal->getWidth());
}
}

return $width;
}

public function getPaddingTopBottom() : int
{
return $this->paddingTopBottom;
}

public function getPaddingLeftRight() : int
{
return $this->paddingLeftRight;
}

private function generatePaddingTopBottomRows() : void
{
if ($this->borderLeftWidth || $this->borderRightWidth) {
$borderColour = $this->getBorderColourCode();
} else {
$borderColour = '';
}

$paddingRow = sprintf(
"%s%s%s%s%s%s%s%s%s%s\n",
$this->debugMode ? $this->getDebugString($this->margin) : str_repeat(' ', $this->margin),
$borderColour,
str_repeat(' ', $this->borderLeftWidth),
$this->getColoursSetCode(),
str_repeat(' ', $this->paddingLeftRight),
str_repeat(' ', $this->contentWidth),
str_repeat(' ', $this->paddingLeftRight),
$borderColour,
str_repeat(' ', $this->borderRightWidth),
$this->coloursResetCode
);


if ($this->debugMode && s::length($paddingRow) <= $this->terminal->getWidth()) {
$paddingRow = substr_replace(
$paddingRow,
sprintf("%s\n", $this->getDebugString($this->terminal->getWidth() - (s::length($paddingRow) - 1))),
-1
);
}

$this->paddingTopBottom = max($this->paddingTopBottom, 0);
$this->paddingTopBottomRows = array_fill(0, $this->paddingTopBottom, $paddingRow);
}




public function getPaddingTopBottomRows() : array
{
return $this->paddingTopBottomRows;
}

public function setPadding(int $topBottom, int $leftRight = null) : self
{
if ($leftRight === null) {
$leftRight = $topBottom;
}

$this->setPaddingTopBottom($topBottom);
$this->setPaddingLeftRight($leftRight);

$this->calculateContentWidth();
$this->generatePaddingTopBottomRows();

return $this;
}

public function setPaddingTopBottom(int $topBottom) : self
{
Assertion::greaterOrEqualThan($topBottom, 0);
$this->paddingTopBottom = $topBottom;

$this->generatePaddingTopBottomRows();

return $this;
}

public function setPaddingLeftRight(int $leftRight) : self
{
Assertion::greaterOrEqualThan($leftRight, 0);
$this->paddingLeftRight = $leftRight;

$this->calculateContentWidth();
$this->generatePaddingTopBottomRows();

return $this;
}

public function getMargin() : int
{
return $this->margin;
}

public function setMarginAuto() : self
{
$this->marginAuto = true;
$this->margin = 0;

$this->setWidth($this->requestedWidth);

return $this;
}

private function calculateMarginAuto(int $width) : void
{
$this->margin = (int) floor(($this->terminal->getWidth() - ($width)) / 2);
}

public function setMargin(int $margin) : self
{
Assertion::greaterOrEqualThan($margin, 0);

$this->marginAuto = false;
$this->margin = $margin;



$this->setWidth($this->requestedWidth);

return $this;
}

public function getContentWidth() : int
{
return $this->contentWidth;
}




public function getRightHandPadding(int $contentLength) : int
{
$rightPadding = $this->getContentWidth() - $contentLength + $this->getPaddingLeftRight();

if ($rightPadding < 0) {
$rightPadding = 0;
}

return $rightPadding;
}

public function setItemExtra(string $itemExtra) : self
{
$this->itemExtra = $itemExtra;

return $this;
}

public function getItemExtra() : string
{
return $this->itemExtra;
}

public function getDisplaysExtra() : bool
{
return $this->displaysExtra;
}

public function setDisplaysExtra(bool $displaysExtra) : self
{
$this->displaysExtra = $displaysExtra;

return $this;
}

public function getTitleSeparator() : string
{
return $this->titleSeparator;
}

public function setTitleSeparator(string $actionSeparator) : self
{
$this->titleSeparator = $actionSeparator;

return $this;
}

private function generateBorderRows() : void
{
$borderRow = sprintf(
"%s%s%s%s\n",
$this->debugMode ? $this->getDebugString($this->margin) : str_repeat(' ', $this->margin),
$this->getBorderColourCode(),
str_repeat(' ', $this->width),
$this->getColoursResetCode()
);

if ($this->debugMode && s::length($borderRow) <= $this->terminal->getWidth()) {
$borderRow = substr_replace(
$borderRow,
sprintf("%s\n", $this->getDebugString($this->terminal->getWidth() - (s::length($borderRow) - 1))),
-1
);
}

$this->borderTopWidth = max($this->borderTopWidth, 0);
$this->borderBottomWidth = max($this->borderBottomWidth, 0);

$this->borderTopRows = array_fill(0, $this->borderTopWidth, $borderRow);
$this->borderBottomRows = array_fill(0, $this->borderBottomWidth, $borderRow);
}




public function getBorderTopRows() : array
{
return $this->borderTopRows;
}




public function getBorderBottomRows() : array
{
return $this->borderBottomRows;
}








public function setBorder(
int $topWidth,
$rightWidth = null,
$bottomWidth = null,
$leftWidth = null,
string $colour = null
) : self {
if (!is_int($rightWidth)) {
$colour = $rightWidth;
$rightWidth = $bottomWidth = $leftWidth = $topWidth;
} elseif (!is_int($bottomWidth)) {
$colour = $bottomWidth;
$bottomWidth = $topWidth;
$leftWidth = $rightWidth;
} elseif (!is_int($leftWidth)) {
$colour = $leftWidth;
$leftWidth = $rightWidth;
}

$this->borderTopWidth = $topWidth;
$this->borderRightWidth = $rightWidth;
$this->borderBottomWidth = $bottomWidth;
$this->borderLeftWidth = $leftWidth;

if (is_string($colour)) {
$this->setBorderColour($colour);
}

$this->calculateContentWidth();
$this->generateBorderRows();
$this->generatePaddingTopBottomRows();

return $this;
}

public function setBorderTopWidth(int $width) : self
{
$this->borderTopWidth = $width;

$this->generateBorderRows();

return $this;
}

public function setBorderRightWidth(int $width) : self
{
$this->borderRightWidth = $width;
$this->calculateContentWidth();

$this->generatePaddingTopBottomRows();

return $this;
}

public function setBorderBottomWidth(int $width) : self
{
$this->borderBottomWidth = $width;

$this->generateBorderRows();

return $this;
}

public function setBorderLeftWidth(int $width) : self
{
$this->borderLeftWidth = $width;
$this->calculateContentWidth();

$this->generatePaddingTopBottomRows();

return $this;
}

public function setBorderColour(string $colour, string $fallback = null) : self
{
$this->borderColour = ColourUtil::validateColour(
$this->terminal,
$colour,
$fallback
);

$this->generateBorderRows();
$this->generatePaddingTopBottomRows();

return $this;
}

public function getBorderTopWidth() : int
{
return $this->borderTopWidth;
}

public function getBorderRightWidth() : int
{
return $this->borderRightWidth;
}

public function getBorderBottomWidth() : int
{
return $this->borderBottomWidth;
}

public function getBorderLeftWidth() : int
{
return $this->borderLeftWidth;
}

public function getBorderColour() : string
{
return $this->borderColour;
}

public function getBorderColourCode() : string
{
if (!ctype_digit($this->borderColour)) {
$borderColourCode = self::$availableBackgroundColors[$this->borderColour];
} else {
$borderColourCode = sprintf("48;5;%s", $this->borderColour);
}

return sprintf("\033[%sm", $borderColourCode);
}










public function getOptionCode(string $string, bool $set = true): string
{
return sprintf("\033[%sm", self::$availableOptions[$string][$set ? 'set' : 'unset']);
}





private function getDebugString(int $length) : string
{
$nums = [];
for ($i = 0, $j = 0; $i < $length; $i++, $j++) {
if ($j === 10) {
$j = 0;
}

$nums[] = $j;
}

return implode('', $nums);
}
}
