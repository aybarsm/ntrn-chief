<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu\MenuItem;

use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\CliMenu\Style\ItemStyle;
use PhpSchool\CliMenu\Util\StringUtil;
use PhpSchool\CliMenu\Style\SelectableStyle;
use function PhpSchool\CliMenu\Util\mapWithKeys;




class SelectableItem implements MenuItemInterface
{



private $text;




private $selectAction;




private $showItemExtra;




private $disabled;




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
return $this->selectAction;
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




public function getStyle() : ItemStyle
{
return $this->style;
}

public function setStyle(SelectableStyle $style) : void
{
$this->style = $style;
}
}
