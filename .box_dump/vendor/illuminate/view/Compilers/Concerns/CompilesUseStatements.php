<?php

namespace Illuminate\View\Compilers\Concerns;

trait CompilesUseStatements
{






protected function compileUse($expression)
{
$segments = explode(',', preg_replace("/[\(\)]/", '', $expression));

$use = ltrim(trim($segments[0], " '\""), '\\');
$as = isset($segments[1]) ? ' as '.trim($segments[1], " '\"") : '';

return "<?php use \\{$use}{$as}; ?>";
}
}
