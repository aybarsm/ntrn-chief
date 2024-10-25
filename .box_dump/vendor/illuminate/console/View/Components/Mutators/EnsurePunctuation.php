<?php

namespace Illuminate\Console\View\Components\Mutators;

class EnsurePunctuation
{






public function __invoke($string)
{
if (! str($string)->endsWith(['.', '?', '!', ':'])) {
return "$string.";
}

return $string;
}
}
