<?php










namespace Symfony\Component\Translation\Loader;

use Symfony\Component\Translation\MessageCatalogue;






class ArrayLoader implements LoaderInterface
{
public function load(mixed $resource, string $locale, string $domain = 'messages'): MessageCatalogue
{
$resource = $this->flatten($resource);
$catalogue = new MessageCatalogue($locale);
$catalogue->add($resource, $domain);

return $catalogue;
}









private function flatten(array $messages): array
{
$result = [];
foreach ($messages as $key => $value) {
if (\is_array($value)) {
foreach ($this->flatten($value) as $k => $v) {
if (null !== $v) {
$result[$key.'.'.$k] = $v;
}
}
} elseif (null !== $value) {
$result[$key] = $value;
}
}

return $result;
}
}
