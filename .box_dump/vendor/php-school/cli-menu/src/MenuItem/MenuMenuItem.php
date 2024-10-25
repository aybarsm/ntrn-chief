<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu\MenuItem;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\CliMenu\Style\ItemStyle;
use PhpSchool\CliMenu\Style\SelectableStyle;




class MenuMenuItem implements MenuItemInterface, PropagatesStyles
{



private $text;




private $subMenu;




private $showItemExtra = false;




private $disabled;




private $style;

public function __construct(
string $text,
CliMenu $subMenu,
bool $disabled = false
) {
$this->text = $text;
$this->subMenu = $subMenu;
$this->disabled = $disabled;

$this->style = new SelectableStyle();
}




public function getRows(MenuStyle $style, bool $selected = false) : array
{
return (new SelectableItemRenderer())->render($style, $this, $selected, $this->disabled);
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
return function (CliMenu $menu) {
$this->showSubMenu($menu);
};
}




public function getSubMenu() : CliMenu
{
return $this->subMenu;
}




public function showSubMenu(CliMenu $parentMenu) : void
{
$parentMenu->closeThis();
$this->subMenu->open();
}




public function canSelect() : bool
{
return !$this->disabled;
}




public function showItemExtra() : void
{
$this->showItemExtra = true;
}




public function showsItemExtra() : bool
{
return $this->showItemExtra;
}




public function hideItemExtra() : void
{
$this->showItemExtra = false;
}




public function getStyle() : ItemStyle
{
return $this->style;
}

public function setStyle(SelectableStyle $style) : void
{
$this->style = $style;
}




public function propagateStyles(CliMenu $parent): void
{
$this->getSubMenu()->importStyles($parent);
$this->getSubMenu()->propagateStyles();
}
}
