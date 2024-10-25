<?php

declare(strict_types=1);

namespace PhpSchool\CliMenu\MenuItem;

use PhpSchool\CliMenu\CliMenu;

interface PropagatesStyles
{




public function propagateStyles(CliMenu $parent) : void;
}
