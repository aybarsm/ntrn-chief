<?php

namespace Laravel\Prompts\Themes\Default\Concerns;

trait InteractsWithStrings
{





protected function longest(array $lines, int $padding = 0): int
{
return max(
$this->minWidth,
count($lines) > 0 ? max(array_map(fn ($line) => mb_strwidth($this->stripEscapeSequences($line)) + $padding, $lines)) : null
);
}




protected function pad(string $text, int $length, string $char = ' '): string
{
$rightPadding = str_repeat($char, max(0, $length - mb_strwidth($this->stripEscapeSequences($text))));

return "{$text}{$rightPadding}";
}




protected function stripEscapeSequences(string $text): string
{

$text = preg_replace("/\e[^m]*m/", '', $text);


$text = preg_replace("/<(info|comment|question|error)>(.*?)<\/\\1>/", '$2', $text);


return preg_replace("/<(?:(?:[fb]g|options)=[a-z,;]+)+>(.*?)<\/>/i", '$1', $text);
}
}