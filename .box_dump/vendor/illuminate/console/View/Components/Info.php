<?php

namespace Illuminate\Console\View\Components;

use Symfony\Component\Console\Output\OutputInterface;

class Info extends Component
{







public function render($string, $verbosity = OutputInterface::VERBOSITY_NORMAL)
{
with(new Line($this->output))->render('info', $string, $verbosity);
}
}
