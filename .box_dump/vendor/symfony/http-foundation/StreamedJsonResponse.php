<?php










namespace Symfony\Component\HttpFoundation;
































class StreamedJsonResponse extends StreamedResponse
{
private const PLACEHOLDER = '__symfony_json__';







public function __construct(
private readonly iterable $data,
int $status = 200,
array $headers = [],
private int $encodingOptions = JsonResponse::DEFAULT_ENCODING_OPTIONS,
) {
parent::__construct($this->stream(...), $status, $headers);

if (!$this->headers->get('Content-Type')) {
$this->headers->set('Content-Type', 'application/json');
}
}

private function stream(): void
{
$jsonEncodingOptions = \JSON_THROW_ON_ERROR | $this->encodingOptions;
$keyEncodingOptions = $jsonEncodingOptions & ~\JSON_NUMERIC_CHECK;

$this->streamData($this->data, $jsonEncodingOptions, $keyEncodingOptions);
}

private function streamData(mixed $data, int $jsonEncodingOptions, int $keyEncodingOptions): void
{
if (\is_array($data)) {
$this->streamArray($data, $jsonEncodingOptions, $keyEncodingOptions);

return;
}

if (is_iterable($data) && !$data instanceof \JsonSerializable) {
$this->streamIterable($data, $jsonEncodingOptions, $keyEncodingOptions);

return;
}

echo json_encode($data, $jsonEncodingOptions);
}

private function streamArray(array $data, int $jsonEncodingOptions, int $keyEncodingOptions): void
{
$generators = [];

array_walk_recursive($data, function (&$item, $key) use (&$generators) {
if (self::PLACEHOLDER === $key) {


$generators[] = $key;
}


if (\is_object($item)) {
$generators[] = $item;
$item = self::PLACEHOLDER;
} elseif (self::PLACEHOLDER === $item) {


$generators[] = $item;
}
});

$jsonParts = explode('"'.self::PLACEHOLDER.'"', json_encode($data, $jsonEncodingOptions));

foreach ($generators as $index => $generator) {

echo $jsonParts[$index];

$this->streamData($generator, $jsonEncodingOptions, $keyEncodingOptions);
}


echo $jsonParts[array_key_last($jsonParts)];
}

private function streamIterable(iterable $iterable, int $jsonEncodingOptions, int $keyEncodingOptions): void
{
$isFirstItem = true;
$startTag = '[';

foreach ($iterable as $key => $item) {
if ($isFirstItem) {
$isFirstItem = false;



if (0 !== $key) {
$startTag = '{';
}

echo $startTag;
} else {

echo ',';
}

if ('{' === $startTag) {
echo json_encode((string) $key, $keyEncodingOptions).':';
}

$this->streamData($item, $jsonEncodingOptions, $keyEncodingOptions);
}

if ($isFirstItem) { 
echo '[';
}

echo '[' === $startTag ? ']' : '}';
}
}
