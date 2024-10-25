<?php










namespace Symfony\Component\Mime\Header;

use Symfony\Component\Mime\Encoder\Rfc2231Encoder;




final class ParameterizedHeader extends UnstructuredHeader
{





public const TOKEN_REGEX = '(?:[\x21\x23-\x27\x2A\x2B\x2D\x2E\x30-\x39\x41-\x5A\x5E-\x7E]+)';

private ?Rfc2231Encoder $encoder = null;
private array $parameters = [];

public function __construct(string $name, string $value, array $parameters = [])
{
parent::__construct($name, $value);

foreach ($parameters as $k => $v) {
$this->setParameter($k, $v);
}

if ('content-type' !== strtolower($name)) {
$this->encoder = new Rfc2231Encoder();
}
}

public function setParameter(string $parameter, ?string $value): void
{
$this->setParameters(array_merge($this->getParameters(), [$parameter => $value]));
}

public function getParameter(string $parameter): string
{
return $this->getParameters()[$parameter] ?? '';
}




public function setParameters(array $parameters): void
{
$this->parameters = $parameters;
}




public function getParameters(): array
{
return $this->parameters;
}

public function getBodyAsString(): string
{
$body = parent::getBodyAsString();
foreach ($this->parameters as $name => $value) {
if (null !== $value) {
$body .= '; '.$this->createParameter($name, $value);
}
}

return $body;
}







protected function toTokens(?string $string = null): array
{
$tokens = parent::toTokens(parent::getBodyAsString());


foreach ($this->parameters as $name => $value) {
if (null !== $value) {

$tokens[\count($tokens) - 1] .= ';';
$tokens = array_merge($tokens, $this->generateTokenLines(' '.$this->createParameter($name, $value)));
}
}

return $tokens;
}




private function createParameter(string $name, string $value): string
{
$origValue = $value;

$encoded = false;

$maxValueLength = $this->getMaxLineLength() - \strlen($name.'=*N"";') - 1;
$firstLineOffset = 0;


if (!preg_match('/^'.self::TOKEN_REGEX.'$/D', $value)) {


if (!preg_match('/^[\x00-\x08\x0B\x0C\x0E-\x7F]*$/D', $value)) {
$encoded = true;

$maxValueLength = $this->getMaxLineLength() - \strlen($name.'*N*="";') - 1;
$firstLineOffset = \strlen($this->getCharset()."'".$this->getLanguage()."'");
}

if (\in_array($name, ['name', 'filename'], true) && 'form-data' === $this->getValue() && 'content-disposition' === strtolower($this->getName()) && preg_match('//u', $value)) {






$value = str_replace(['"', "\r", "\n"], ['%22', '%0D', '%0A'], $value);

if (\strlen($value) <= $maxValueLength) {
return $name.'="'.$value.'"';
}

$value = $origValue;
}
}


if ($encoded || \strlen($value) > $maxValueLength) {
if (null !== $this->encoder) {
$value = $this->encoder->encodeString($origValue, $this->getCharset(), $firstLineOffset, $maxValueLength);
} else {

$value = $this->getTokenAsEncodedWord($origValue);
$encoded = false;
}
}

$valueLines = $this->encoder ? explode("\r\n", $value) : [$value];


if (\count($valueLines) > 1) {
$paramLines = [];
foreach ($valueLines as $i => $line) {
$paramLines[] = $name.'*'.$i.$this->getEndOfParameterValue($line, true, 0 === $i);
}

return implode(";\r\n ", $paramLines);
} else {
return $name.$this->getEndOfParameterValue($valueLines[0], $encoded, true);
}
}






private function getEndOfParameterValue(string $value, bool $encoded = false, bool $firstLine = false): string
{
$forceHttpQuoting = 'form-data' === $this->getValue() && 'content-disposition' === strtolower($this->getName());
if ($forceHttpQuoting || !preg_match('/^'.self::TOKEN_REGEX.'$/D', $value)) {
$value = '"'.$value.'"';
}
$prepend = '=';
if ($encoded) {
$prepend = '*=';
if ($firstLine) {
$prepend = '*='.$this->getCharset()."'".$this->getLanguage()."'";
}
}

return $prepend.$value;
}
}
