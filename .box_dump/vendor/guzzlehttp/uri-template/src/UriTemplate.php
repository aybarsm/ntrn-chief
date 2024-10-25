<?php

declare(strict_types=1);

namespace GuzzleHttp\UriTemplate;






final class UriTemplate
{



private static $operatorHash = [
'' => ['prefix' => '', 'joiner' => ',', 'query' => false],
'+' => ['prefix' => '', 'joiner' => ',', 'query' => false],
'#' => ['prefix' => '#', 'joiner' => ',', 'query' => false],
'.' => ['prefix' => '.', 'joiner' => '.', 'query' => false],
'/' => ['prefix' => '/', 'joiner' => '/', 'query' => false],
';' => ['prefix' => ';', 'joiner' => ';', 'query' => true],
'?' => ['prefix' => '?', 'joiner' => '&', 'query' => true],
'&' => ['prefix' => '&', 'joiner' => '&', 'query' => true],
];




private static $delims = [
':',
'/',
'?',
'#',
'[',
']',
'@',
'!',
'$',
'&',
'\'',
'(',
')',
'*',
'+',
',',
';',
'=',
];




private static $delimsPct = [
'%3A',
'%2F',
'%3F',
'%23',
'%5B',
'%5D',
'%40',
'%21',
'%24',
'%26',
'%27',
'%28',
'%29',
'%2A',
'%2B',
'%2C',
'%3B',
'%3D',
];






public static function expand(string $template, array $variables): string
{
if (false === \strpos($template, '{')) {
return $template;
}


$result = \preg_replace_callback(
'/\{([^\}]+)\}/',
self::expandMatchCallback($variables),
$template
);

if (null === $result) {
throw new \RuntimeException(\sprintf('Unable to process template: %s', \preg_last_error_msg()));
}

return $result;
}






private static function expandMatchCallback(array $variables): callable
{
return static function (array $matches) use ($variables): string {
return self::expandMatch($matches, $variables);
};
}









private static function expandMatch(array $matches, array $variables): string
{
$replacements = [];
$parsed = self::parseExpression($matches[1]);
$prefix = self::$operatorHash[$parsed['operator']]['prefix'];
$joiner = self::$operatorHash[$parsed['operator']]['joiner'];
$useQuery = self::$operatorHash[$parsed['operator']]['query'];
$allUndefined = true;

foreach ($parsed['values'] as $value) {
if (!isset($variables[$value['value']])) {
continue;
}

$variable = $variables[$value['value']];
$actuallyUseQuery = $useQuery;
$expanded = '';

if (\is_array($variable)) {
$isAssoc = self::isAssoc($variable);
$kvp = [];

foreach ($variable as $key => $var) {
if ($isAssoc) {
$key = \rawurlencode((string) $key);
$isNestedArray = \is_array($var);
} else {
$isNestedArray = false;
}

if (!$isNestedArray) {
$var = \rawurlencode((string) $var);
if ($parsed['operator'] === '+' || $parsed['operator'] === '#') {
$var = self::decodeReserved($var);
}
}

if ($value['modifier'] === '*') {
if ($isAssoc) {
if ($isNestedArray) {

$var = \http_build_query([$key => $var], '', '&', \PHP_QUERY_RFC3986);
} else {
$var = \sprintf('%s=%s', (string) $key, (string) $var);
}
} elseif ($key > 0 && $actuallyUseQuery) {
$var = \sprintf('%s=%s', $value['value'], (string) $var);
}
}


$kvp[$key] = $var;
}

if (0 === \count($variable)) {
$actuallyUseQuery = false;
} elseif ($value['modifier'] === '*') {
$expanded = \implode($joiner, $kvp);
if ($isAssoc) {


$actuallyUseQuery = false;
}
} else {
if ($isAssoc) {




foreach ($kvp as $k => &$v) {
$v = \sprintf('%s,%s', $k, $v);
}
}
$expanded = \implode(',', $kvp);
}
} else {
$allUndefined = false;
if ($value['modifier'] === ':' && isset($value['position'])) {
$variable = \substr((string) $variable, 0, $value['position']);
}
$expanded = \rawurlencode((string) $variable);
if ($parsed['operator'] === '+' || $parsed['operator'] === '#') {
$expanded = self::decodeReserved($expanded);
}
}

if ($actuallyUseQuery) {
if ($expanded === '' && $joiner !== '&') {
$expanded = $value['value'];
} else {
$expanded = \sprintf('%s=%s', $value['value'], $expanded);
}
}

$replacements[] = $expanded;
}

$ret = \implode($joiner, $replacements);

if ('' === $ret) {

if (false === $allUndefined && ('#' === $prefix || '.' === $prefix)) {
return $prefix;
}
} else {
if ('' !== $prefix) {
return \sprintf('%s%s', $prefix, $ret);
}
}

return $ret;
}








private static function parseExpression(string $expression): array
{
$result = [];

if (isset(self::$operatorHash[$expression[0]])) {
$result['operator'] = $expression[0];

$expression = \substr($expression, 1);
} else {
$result['operator'] = '';
}

$result['values'] = [];
foreach (\explode(',', $expression) as $value) {
$value = \trim($value);
$varspec = [];
if ($colonPos = \strpos($value, ':')) {
$varspec['value'] = (string) \substr($value, 0, $colonPos);
$varspec['modifier'] = ':';
$varspec['position'] = (int) \substr($value, $colonPos + 1);
} elseif (\substr($value, -1) === '*') {
$varspec['modifier'] = '*';
$varspec['value'] = (string) \substr($value, 0, -1);
} else {
$varspec['value'] = $value;
$varspec['modifier'] = '';
}
$result['values'][] = $varspec;
}

return $result;
}









private static function isAssoc(array $array): bool
{
return $array && \array_keys($array)[0] !== 0;
}





private static function decodeReserved(string $string): string
{
return \str_replace(self::$delimsPct, self::$delims, $string);
}
}
