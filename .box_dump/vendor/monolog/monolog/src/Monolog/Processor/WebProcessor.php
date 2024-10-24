<?php declare(strict_types=1);










namespace Monolog\Processor;

use ArrayAccess;
use Monolog\LogRecord;






class WebProcessor implements ProcessorInterface
{



protected array|ArrayAccess $serverData;








protected array $extraFields = [
'url' => 'REQUEST_URI',
'ip' => 'REMOTE_ADDR',
'http_method' => 'REQUEST_METHOD',
'server' => 'SERVER_NAME',
'referrer' => 'HTTP_REFERER',
'user_agent' => 'HTTP_USER_AGENT',
];





public function __construct(array|ArrayAccess|null $serverData = null, array|null $extraFields = null)
{
if (null === $serverData) {
$this->serverData = &$_SERVER;
} else {
$this->serverData = $serverData;
}

$defaultEnabled = ['url', 'ip', 'http_method', 'server', 'referrer'];
if (isset($this->serverData['UNIQUE_ID'])) {
$this->extraFields['unique_id'] = 'UNIQUE_ID';
$defaultEnabled[] = 'unique_id';
}

if (null === $extraFields) {
$extraFields = $defaultEnabled;
}
if (isset($extraFields[0])) {
foreach (array_keys($this->extraFields) as $fieldName) {
if (!\in_array($fieldName, $extraFields, true)) {
unset($this->extraFields[$fieldName]);
}
}
} else {
$this->extraFields = $extraFields;
}
}




public function __invoke(LogRecord $record): LogRecord
{


if (!isset($this->serverData['REQUEST_URI'])) {
return $record;
}

$record->extra = $this->appendExtraFields($record->extra);

return $record;
}




public function addExtraField(string $extraName, string $serverName): self
{
$this->extraFields[$extraName] = $serverName;

return $this;
}





private function appendExtraFields(array $extra): array
{
foreach ($this->extraFields as $extraName => $serverName) {
$extra[$extraName] = $this->serverData[$serverName] ?? null;
}

return $extra;
}
}
