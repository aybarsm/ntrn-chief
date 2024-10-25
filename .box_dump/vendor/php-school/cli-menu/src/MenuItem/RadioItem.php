<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu\MenuItem;

use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\CliMenu\Style\ItemStyle;
use PhpSchool\CliMenu\Style\RadioStyle;

class RadioItem implements MenuItemInterface
{



private $text;




private $selectAction;




private $showItemExtra;




private $disabled;






private $checked = false;




private $style;

public function __construct(
string $text,
callable $selectAction,
bool $showItemExtra = false,
bool $disabled = false
) {
$this->text = $text;
$this->selectAction = $selectAction;
$this->showItemExtra = $showItemExtra;
$this->disabled = $disabled;

$this->style = new RadioStyle();
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
return function (CliMenu $cliMenu) {
$parentItem = $cliMenu->getItemByIndex($cliMenu->getSelectedItemIndex());

$siblings = $parentItem instanceof SplitItem
? $parentItem->getItems()
: $cliMenu->getItems();

$filtered = array_filter(
$siblings,
function (MenuItemInterface $item) {
return $item instanceof self;
}
);

array_walk(
$filtered,
function (RadioItem $item) {
$item->setUnchecked();
}
);

$this->setChecked();
$cliMenu->redraw();

return ($this->selectAction)($cliMenu);
};
}




public function canSelect() : bool
{
return !$this->disabled;
}




public function showsItemExtra() : bool
{
return $this->showItemExtra;
}




public function showItemExtra() : void
{
$this->showItemExtra = true;
}




public function hideItemExtra() : void
{
$this->showItemExtra = false;
}




public function getChecked() : bool
{
return $this->checked;
}




public function setChecked() : void
{
$this->checked = true;
}




public function setUnchecked() : void
{
$this->checked = false;
}




public function toggle() : void
{
$this->checked = !$this->checked;
}




public function getStyle() : ItemStyle
{
return $this->style;
}

public function setStyle(RadioStyle $style) : void
{
$this->style = $style;
}
}
