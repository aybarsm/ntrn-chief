<?php











namespace Humbug\SelfUpdate\Strategy;

use Humbug\SelfUpdate\Exception\HttpRequestException;
use Humbug\SelfUpdate\Exception\InvalidArgumentException;
use Humbug\SelfUpdate\Updater;

use function file_get_contents;

abstract class ShaStrategyAbstract implements StrategyInterface
{

const SUPPORTED_SCHEMES = [
'http',
'https',
'file',
];




protected $versionUrl;




protected $pharUrl;






public function download(Updater $updater)
{

set_error_handler([$updater, 'throwHttpRequestException']);
$result = file_get_contents($this->getPharUrl());
restore_error_handler();
if (false === $result) {
throw new HttpRequestException(sprintf(
'Request to URL failed: %s',
$this->getPharUrl()
));
}

file_put_contents($updater->getTempPharFile(), $result);
}






public function setPharUrl($url)
{
if (! $this->validateAllowedUrl($url)) {
throw new InvalidArgumentException(
sprintf('Invalid url passed as argument: %s.', $url)
);
}
$this->pharUrl = $url;
}






public function getPharUrl()
{
return $this->pharUrl;
}






public function setVersionUrl($url)
{
if (! $this->validateAllowedUrl($url)) {
throw new InvalidArgumentException(
sprintf('Invalid url passed as argument: %s.', $url)
);
}
$this->versionUrl = $url;
}






public function getVersionUrl()
{
return $this->versionUrl;
}

protected function validateAllowedUrl($url)
{
return
filter_var($url, FILTER_VALIDATE_URL)
&& in_array(parse_url($url, PHP_URL_SCHEME), self::SUPPORTED_SCHEMES);
}
}
