<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu\MenuItem;

use PhpSchool\CliMenu\MenuStyle;
use PhpSchool\CliMenu\Style\ItemStyle;




interface MenuItemInterface
{



public function getRows(MenuStyle $style, bool $selected = false) : array;




public function getText() : string;




public function canSelect() : bool;




public function getSelectAction() : ?callable;




public function showsItemExtra() : bool;




public function showItemExtra() : void;




public function hideItemExtra() : void;






public function getStyle() : ItemStyle;
}
