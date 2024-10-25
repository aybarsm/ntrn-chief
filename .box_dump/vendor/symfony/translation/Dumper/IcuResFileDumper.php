<?php










namespace Symfony\Component\Translation\Dumper;

use Symfony\Component\Translation\MessageCatalogue;






class IcuResFileDumper extends FileDumper
{
protected string $relativePathTemplate = '%domain%/%locale%.%extension%';

public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []): string
{
$data = $indexes = $resources = '';

foreach ($messages->all($domain) as $source => $target) {
$indexes .= pack('v', \strlen($data) + 28);
$data .= $source."\0";
}

$data .= $this->writePadding($data);

$keyTop = $this->getPosition($data);

foreach ($messages->all($domain) as $source => $target) {
$resources .= pack('V', $this->getPosition($data));

$data .= pack('V', \strlen($target))
.mb_convert_encoding($target."\0", 'UTF-16LE', 'UTF-8')
.$this->writePadding($data)
;
}

$resOffset = $this->getPosition($data);

$data .= pack('v', \count($messages->all($domain)))
.$indexes
.$this->writePadding($data)
.$resources
;

$bundleTop = $this->getPosition($data);

$root = pack('V7',
$resOffset + (2 << 28), 
6, 
$keyTop, 
$bundleTop, 
$bundleTop, 
\count($messages->all($domain)), 
0 
);

$header = pack('vC2v4C12@32',
32, 
0xDA, 0x27, 
20, 0, 0, 2, 
0x52, 0x65, 0x73, 0x42, 
1, 2, 0, 0, 
1, 4, 0, 0 
);

return $header.$root.$data;
}

private function writePadding(string $data): ?string
{
$padding = \strlen($data) % 4;

return $padding ? str_repeat("\xAA", 4 - $padding) : null;
}

private function getPosition(string $data): float|int
{
return (\strlen($data) + 28) / 4;
}

protected function getExtension(): string
{
return 'res';
}
}
