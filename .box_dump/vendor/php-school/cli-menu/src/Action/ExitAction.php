<?php
declare(strict_types=1);

namespace PhpSchool\CliMenu\Action;

use PhpSchool\CliMenu\CliMenu;




class ExitAction
{
public function __invoke(CliMenu $menu) : void
{
$menu->close();
}
}
