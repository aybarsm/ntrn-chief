<?php

namespace Illuminate\Support;






class ProcessUtils
{






public static function escapeArgument($argument)
{




if ('\\' === DIRECTORY_SEPARATOR) {
if ($argument === '') {
return '""';
}

$escapedArgument = '';
$quote = false;

foreach (preg_split('/(")/', $argument, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $part) {
if ($part === '"') {
$escapedArgument .= '\\"';
} elseif (self::isSurroundedBy($part, '%')) {

$escapedArgument .= '^%"'.substr($part, 1, -1).'"^%';
} else {

if (str_ends_with($part, '\\')) {
$part .= '\\';
}
$quote = true;
$escapedArgument .= $part;
}
}

if ($quote) {
$escapedArgument = '"'.$escapedArgument.'"';
}

return $escapedArgument;
}

return "'".str_replace("'", "'\\''", $argument)."'";
}








protected static function isSurroundedBy($arg, $char)
{
return strlen($arg) > 2 && $char === $arg[0] && $char === $arg[strlen($arg) - 1];
}
}
