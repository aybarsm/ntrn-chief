<?php

namespace Illuminate\Console\View\Components\Mutators;

class EnsureNoPunctuation
{






public function __invoke($string)
{
if (str($string)->endsWith(['.', '?', '!', ':'])) {
return substr_replace($string, '', -1);
}

return $string;
}
}
