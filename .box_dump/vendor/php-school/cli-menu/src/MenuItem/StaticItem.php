<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu\MenuItem;

use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\CliMenu\Style\DefaultStyle;
use PhpSchool\CliMenu\Style\ItemStyle;
use PhpSchool\CliMenu\Util\StringUtil;




class StaticItem implements MenuItemInterface
{



private $text;




private $style;

public function __construct(string $text)
{
$this->text = $text;

$this->style = new DefaultStyle();
}




public function getRows(MenuStyle $style, bool $selected = false) : array
{
return explode("\n", StringUtil::wordwrap($this->text, $style->getContentWidth()));
}




public function getText() : string
{
return $this->text;
}




public function setText(string $text) : void
{
$this->text = $text;
}




public function getSelectAction() : ?callable
{
return null;
}




public function canSelect() : bool
{
return false;
}




public function showsItemExtra() : bool
{
return false;
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
