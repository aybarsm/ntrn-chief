<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu\MenuItem;

use Assert\Assertion;
use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\CliMenu\Style\DefaultStyle;
use PhpSchool\CliMenu\Style\ItemStyle;




class LineBreakItem implements MenuItemInterface
{



private $breakChar;




private $lines;




private $style;

public function __construct(string $breakChar = ' ', int $lines = 1)
{
$this->breakChar = $breakChar;
$this->lines = $lines;

$this->style = new DefaultStyle();
}




public function getRows(MenuStyle $style, bool $selected = false) : array
{
return explode(
"\n",
rtrim(str_repeat(sprintf(
"%s\n",
mb_substr(str_repeat($this->breakChar, $style->getContentWidth()), 0, $style->getContentWidth())
), $this->lines))
);
}




public function canSelect() : bool
{
return false;
}




public function getSelectAction() : ?callable
{
return null;
}




public function getText() : string
{
return $this->breakChar;
}




public function setText(string $text) : void
{
$this->breakChar = $text;
}




public function showsItemExtra() : bool
{
return false;
}

public function getLines() : int
{
return $this->lines;
}




public function showItemExtra() : void
{

}




public function hideItemExtra() : void
{

}




public function getStyle() : ItemStyle
{
return $this->style;
}

public function setStyle(DefaultStyle $style) : void
{
$this->style = $style;
}
}
