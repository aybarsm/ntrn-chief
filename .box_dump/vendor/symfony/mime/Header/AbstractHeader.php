<?php










namespace Symfony\Component\Mime\Header;

use Symfony\Component\Mime\Encoder\QpMimeHeaderEncoder;






abstract class AbstractHeader implements HeaderInterface
{
public const PHRASE_PATTERN = '(?:(?:(?:(?:(?:(?:(?:[ \t]*(?:\r\n))?[ \t])?(\((?:(?:(?:[ \t]*(?:\r\n))?[ \t])|(?:(?:[\x01-\x08\x0B\x0C\x0E-\x19\x7F]|[\x21-\x27\x2A-\x5B\x5D-\x7E])|(?:\\[\x00-\x08\x0B\x0C\x0E-\x7F])|(?1)))*(?:(?:[ \t]*(?:\r\n))?[ \t])?\)))*(?:(?:(?:(?:[ \t]*(?:\r\n))?[ \t])?(\((?:(?:(?:[ \t]*(?:\r\n))?[ \t])|(?:(?:[\x01-\x08\x0B\x0C\x0E-\x19\x7F]|[\x21-\x27\x2A-\x5B\x5D-\x7E])|(?:\\[\x00-\x08\x0B\x0C\x0E-\x7F])|(?1)))*(?:(?:[ \t]*(?:\r\n))?[ \t])?\)))|(?:(?:[ \t]*(?:\r\n))?[ \t])))?[a-zA-Z0-9!#\$%&\'\*\+\-\/=\?\^_`\{\}\|~]+(?:(?:(?:(?:[ \t]*(?:\r\n))?[ \t])?(\((?:(?:(?:[ \t]*(?:\r\n))?[ \t])|(?:(?:[\x01-\x08\x0B\x0C\x0E-\x19\x7F]|[\x21-\x27\x2A-\x5B\x5D-\x7E])|(?:\\[\x00-\x08\x0B\x0C\x0E-\x7F])|(?1)))*(?:(?:[ \t]*(?:\r\n))?[ \t])?\)))*(?:(?:(?:(?:[ \t]*(?:\r\n))?[ \t])?(\((?:(?:(?:[ \t]*(?:\r\n))?[ \t])|(?:(?:[\x01-\x08\x0B\x0C\x0E-\x19\x7F]|[\x21-\x27\x2A-\x5B\x5D-\x7E])|(?:\\[\x00-\x08\x0B\x0C\x0E-\x7F])|(?1)))*(?:(?:[ \t]*(?:\r\n))?[ \t])?\)))|(?:(?:[ \t]*(?:\r\n))?[ \t])))?)|(?:(?:(?:(?:(?:[ \t]*(?:\r\n))?[ \t])?(\((?:(?:(?:[ \t]*(?:\r\n))?[ \t])|(?:(?:[\x01-\x08\x0B\x0C\x0E-\x19\x7F]|[\x21-\x27\x2A-\x5B\x5D-\x7E])|(?:\\[\x00-\x08\x0B\x0C\x0E-\x7F])|(?1)))*(?:(?:[ \t]*(?:\r\n))?[ \t])?\)))*(?:(?:(?:(?:[ \t]*(?:\r\n))?[ \t])?(\((?:(?:(?:[ \t]*(?:\r\n))?[ \t])|(?:(?:[\x01-\x08\x0B\x0C\x0E-\x19\x7F]|[\x21-\x27\x2A-\x5B\x5D-\x7E])|(?:\\[\x00-\x08\x0B\x0C\x0E-\x7F])|(?1)))*(?:(?:[ \t]*(?:\r\n))?[ \t])?\)))|(?:(?:[ \t]*(?:\r\n))?[ \t])))?"((?:(?:[ \t]*(?:\r\n))?[ \t])?(?:(?:[\x01-\x08\x0B\x0C\x0E-\x19\x7F]|[\x21\x23-\x5B\x5D-\x7E])|(?:\\[\x00-\x08\x0B\x0C\x0E-\x7F])))*(?:(?:[ \t]*(?:\r\n))?[ \t])?"(?:(?:(?:(?:[ \t]*(?:\r\n))?[ \t])?(\((?:(?:(?:[ \t]*(?:\r\n))?[ \t])|(?:(?:[\x01-\x08\x0B\x0C\x0E-\x19\x7F]|[\x21-\x27\x2A-\x5B\x5D-\x7E])|(?:\\[\x00-\x08\x0B\x0C\x0E-\x7F])|(?1)))*(?:(?:[ \t]*(?:\r\n))?[ \t])?\)))*(?:(?:(?:(?:[ \t]*(?:\r\n))?[ \t])?(\((?:(?:(?:[ \t]*(?:\r\n))?[ \t])|(?:(?:[\x01-\x08\x0B\x0C\x0E-\x19\x7F]|[\x21-\x27\x2A-\x5B\x5D-\x7E])|(?:\\[\x00-\x08\x0B\x0C\x0E-\x7F])|(?1)))*(?:(?:[ \t]*(?:\r\n))?[ \t])?\)))|(?:(?:[ \t]*(?:\r\n))?[ \t])))?))+?)';

private static QpMimeHeaderEncoder $encoder;

private string $name;
private int $lineLength = 76;
private ?string $lang = null;
private string $charset = 'utf-8';

public function __construct(string $name)
{
$this->name = $name;
}

public function setCharset(string $charset): void
{
$this->charset = $charset;
}

public function getCharset(): ?string
{
return $this->charset;
}






public function setLanguage(string $lang): void
{
$this->lang = $lang;
}

public function getLanguage(): ?string
{
return $this->lang;
}

public function getName(): string
{
return $this->name;
}

public function setMaxLineLength(int $lineLength): void
{
$this->lineLength = $lineLength;
}

public function getMaxLineLength(): int
{
return $this->lineLength;
}

public function toString(): string
{
return $this->tokensToString($this->toTokens());
}







protected function createPhrase(HeaderInterface $header, string $string, string $charset, bool $shorten = false): string
{

$phraseStr = $string;


if (!preg_match('/^'.self::PHRASE_PATTERN.'$/D', $phraseStr)) {


if (preg_match('/^[\x00-\x08\x0B\x0C\x0E-\x7F]*$/D', $phraseStr)) {
foreach (['\\', '"'] as $char) {
$phraseStr = str_replace($char, '\\'.$char, $phraseStr);
}
$phraseStr = '"'.$phraseStr.'"';
} else {


if ($shorten) {
$usedLength = \strlen($header->getName().': ');
} else {
$usedLength = 0;
}
$phraseStr = $this->encodeWords($header, $string, $usedLength);
}
} elseif (str_contains($phraseStr, '(')) {
foreach (['\\', '"'] as $char) {
$phraseStr = str_replace($char, '\\'.$char, $phraseStr);
}
$phraseStr = '"'.$phraseStr.'"';
}

return $phraseStr;
}




protected function encodeWords(HeaderInterface $header, string $input, int $usedLength = -1): string
{
$value = '';
$tokens = $this->getEncodableWordTokens($input);
foreach ($tokens as $token) {

if ($this->tokenNeedsEncoding($token)) {

$firstChar = substr($token, 0, 1);
switch ($firstChar) {
case ' ':
case "\t":
$value .= $firstChar;
$token = substr($token, 1);
}

if (-1 == $usedLength) {
$usedLength = \strlen($header->getName().': ') + \strlen($value);
}
$value .= $this->getTokenAsEncodedWord($token, $usedLength);
} else {
$value .= $token;
}
}

return $value;
}

protected function tokenNeedsEncoding(string $token): bool
{
return (bool) preg_match('~[\x00-\x08\x10-\x19\x7F-\xFF\r\n]~', $token);
}






protected function getEncodableWordTokens(string $string): array
{
$tokens = [];
$encodedToken = '';

foreach (preg_split('~(?=[\t ])~', $string) as $token) {
if ($this->tokenNeedsEncoding($token)) {
$encodedToken .= $token;
} else {
if ('' !== $encodedToken) {
$tokens[] = $encodedToken;
$encodedToken = '';
}
$tokens[] = $token;
}
}
if ('' !== $encodedToken) {
$tokens[] = $encodedToken;
}

return $tokens;
}




protected function getTokenAsEncodedWord(string $token, int $firstLineOffset = 0): string
{
self::$encoder ??= new QpMimeHeaderEncoder();


$charsetDecl = $this->charset;
if (null !== $this->lang) {
$charsetDecl .= '*'.$this->lang;
}
$encodingWrapperLength = \strlen('=?'.$charsetDecl.'?'.self::$encoder->getName().'??=');

if ($firstLineOffset >= 75) {

$firstLineOffset = 0;
}

$encodedTextLines = explode("\r\n",
self::$encoder->encodeString($token, $this->charset, $firstLineOffset, 75 - $encodingWrapperLength)
);

if ('iso-2022-jp' !== strtolower($this->charset)) {

foreach ($encodedTextLines as $lineNum => $line) {
$encodedTextLines[$lineNum] = '=?'.$charsetDecl.'?'.self::$encoder->getName().'?'.$line.'?=';
}
}

return implode("\r\n ", $encodedTextLines);
}






protected function generateTokenLines(string $token): array
{
return preg_split('~(\r\n)~', $token, -1, \PREG_SPLIT_DELIM_CAPTURE);
}




protected function toTokens(?string $string = null): array
{
$string ??= $this->getBodyAsString();

$tokens = [];

foreach (preg_split('~(?=[ \t])~', $string) as $token) {
$newTokens = $this->generateTokenLines($token);
foreach ($newTokens as $newToken) {
$tokens[] = $newToken;
}
}

return $tokens;
}







private function tokensToString(array $tokens): string
{
$lineCount = 0;
$headerLines = [];
$headerLines[] = $this->name.': ';
$currentLine = &$headerLines[$lineCount++];


foreach ($tokens as $i => $token) {

if (("\r\n" === $token)
|| ($i > 0 && \strlen($currentLine.$token) > $this->lineLength)
&& '' !== $currentLine) {
$headerLines[] = '';
$currentLine = &$headerLines[$lineCount++];
}


if ("\r\n" !== $token) {
$currentLine .= $token;
}
}


return implode("\r\n", $headerLines);
}
}
