<?php

namespace Illuminate\Console;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Parser
{








public static function parse(string $expression)
{
$name = static::name($expression);

if (preg_match_all('/\{\s*(.*?)\s*\}/', $expression, $matches) && count($matches[1])) {
return array_merge([$name], static::parameters($matches[1]));
}

return [$name, [], []];
}









protected static function name(string $expression)
{
if (! preg_match('/[^\s]+/', $expression, $matches)) {
throw new InvalidArgumentException('Unable to determine command name from signature.');
}

return $matches[0];
}







protected static function parameters(array $tokens)
{
$arguments = [];

$options = [];

foreach ($tokens as $token) {
if (preg_match('/^-{2,}(.*)/', $token, $matches)) {
$options[] = static::parseOption($matches[1]);
} else {
$arguments[] = static::parseArgument($token);
}
}

return [$arguments, $options];
}







protected static function parseArgument(string $token)
{
[$token, $description] = static::extractDescription($token);

switch (true) {
case str_ends_with($token, '?*'):
return new InputArgument(trim($token, '?*'), InputArgument::IS_ARRAY, $description);
case str_ends_with($token, '*'):
return new InputArgument(trim($token, '*'), InputArgument::IS_ARRAY | InputArgument::REQUIRED, $description);
case str_ends_with($token, '?'):
return new InputArgument(trim($token, '?'), InputArgument::OPTIONAL, $description);
case preg_match('/(.+)\=\*(.+)/', $token, $matches):
return new InputArgument($matches[1], InputArgument::IS_ARRAY, $description, preg_split('/,\s?/', $matches[2]));
case preg_match('/(.+)\=(.+)/', $token, $matches):
return new InputArgument($matches[1], InputArgument::OPTIONAL, $description, $matches[2]);
default:
return new InputArgument($token, InputArgument::REQUIRED, $description);
}
}







protected static function parseOption(string $token)
{
[$token, $description] = static::extractDescription($token);

$matches = preg_split('/\s*\|\s*/', $token, 2);

$shortcut = null;

if (isset($matches[1])) {
$shortcut = $matches[0];
$token = $matches[1];
}

switch (true) {
case str_ends_with($token, '='):
return new InputOption(trim($token, '='), $shortcut, InputOption::VALUE_OPTIONAL, $description);
case str_ends_with($token, '=*'):
return new InputOption(trim($token, '=*'), $shortcut, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, $description);
case preg_match('/(.+)\=\*(.+)/', $token, $matches):
return new InputOption($matches[1], $shortcut, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, $description, preg_split('/,\s?/', $matches[2]));
case preg_match('/(.+)\=(.+)/', $token, $matches):
return new InputOption($matches[1], $shortcut, InputOption::VALUE_OPTIONAL, $description, $matches[2]);
default:
return new InputOption($token, $shortcut, InputOption::VALUE_NONE, $description);
}
}







protected static function extractDescription(string $token)
{
$parts = preg_split('/\s+:\s+/', trim($token), 2);

return count($parts) === 2 ? $parts : [$token, ''];
}
}
