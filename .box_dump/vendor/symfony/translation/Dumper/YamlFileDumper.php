<?php










namespace Symfony\Component\Translation\Dumper;

use Symfony\Component\Translation\Exception\LogicException;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Util\ArrayConverter;
use Symfony\Component\Yaml\Yaml;






class YamlFileDumper extends FileDumper
{
private string $extension;

public function __construct(string $extension = 'yml')
{
$this->extension = $extension;
}

public function formatCatalogue(MessageCatalogue $messages, string $domain, array $options = []): string
{
if (!class_exists(Yaml::class)) {
throw new LogicException('Dumping translations in the YAML format requires the Symfony Yaml component.');
}

$data = $messages->all($domain);

if (isset($options['as_tree']) && $options['as_tree']) {
$data = ArrayConverter::expandToTree($data);
}

if (isset($options['inline']) && ($inline = (int) $options['inline']) > 0) {
return Yaml::dump($data, $inline);
}

return Yaml::dump($data);
}

protected function getExtension(): string
{
return $this->extension;
}
}
