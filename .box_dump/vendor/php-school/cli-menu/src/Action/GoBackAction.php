<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu\Action;

use PhpSchool\CliMenu\CliMenu;




class GoBackAction
{
public function __invoke(CliMenu $menu) : void
{
if ($parent = $menu->getParent()) {
$menu->closeThis();
$parent->open();
}
}
}
