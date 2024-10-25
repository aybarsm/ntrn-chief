<?php











namespace Humbug\SelfUpdate\Strategy;

use Humbug\SelfUpdate\Exception\HttpRequestException;
use Humbug\SelfUpdate\Exception\InvalidArgumentException;
use Humbug\SelfUpdate\Exception\JsonParsingException;
use Humbug\SelfUpdate\Updater;
use Humbug\SelfUpdate\VersionParser;

use function file_get_contents;

class GithubStrategy implements StrategyInterface
{
const API_URL = 'https://packagist.org/p2/%s.json';

const STABLE = 'stable';

const UNSTABLE = 'unstable';

const ANY = 'any';




private $localVersion;




private $remoteVersion;




private $remoteUrl;




private $pharName;




private $packageName;




private $stability = self::STABLE;






public function download(Updater $updater)
{

set_error_handler([$updater, 'throwHttpRequestException']);
$result = file_get_contents($this->remoteUrl);
restore_error_handler();
if (false === $result) {
throw new HttpRequestException(sprintf(
'Request to URL failed: %s',
$this->remoteUrl
));
}

file_put_contents($updater->getTempPharFile(), $result);
}






public function getCurrentRemoteVersion(Updater $updater)
{

set_error_handler([$updater, 'throwHttpRequestException']);
$packageUrl = $this->getApiUrl();
$package = json_decode(file_get_contents($packageUrl), true);
restore_error_handler();

if (null === $package || json_last_error() !== JSON_ERROR_NONE) {
throw new JsonParsingException(
'Error parsing JSON package data'
.(function_exists('json_last_error_msg') ? ': '.json_last_error_msg() : '')
);
}

$versions = array_column($package['packages'][$this->getPackageName()], 'version');
$versionParser = new VersionParser($versions);
if ($this->getStability() === self::STABLE) {
$this->remoteVersion = $versionParser->getMostRecentStable();
} elseif ($this->getStability() === self::UNSTABLE) {
$this->remoteVersion = $versionParser->getMostRecentUnstable();
} else {
$this->remoteVersion = $versionParser->getMostRecentAll();
}




if (! empty($this->remoteVersion)) {
$remoteVersionPackages = array_filter($package['packages'][$this->getPackageName()], function (array $package) {
return $package['version'] === $this->remoteVersion;
});
$chosenVersion = reset($remoteVersionPackages);

$this->remoteUrl = $this->getDownloadUrl($chosenVersion);
}

return $this->remoteVersion;
}






public function getCurrentLocalVersion(Updater $updater)
{
return $this->localVersion;
}






public function setCurrentLocalVersion($version)
{
$this->localVersion = $version;
}






public function setPackageName($name)
{
$this->packageName = $name;
}






public function getPackageName()
{
return $this->packageName;
}






public function setPharName($name)
{
$this->pharName = $name;
}






public function getPharName()
{
return $this->pharName;
}






public function setStability($stability)
{
if ($stability !== self::STABLE && $stability !== self::UNSTABLE && $stability !== self::ANY) {
throw new InvalidArgumentException(
'Invalid stability value. Must be one of "stable", "unstable" or "any".'
);
}
$this->stability = $stability;
}






public function getStability()
{
return $this->stability;
}

protected function getApiUrl()
{
return sprintf(self::API_URL, $this->getPackageName());
}


protected function getDownloadUrl(array $package)
{
$baseUrl = preg_replace(
'{\.git$}',
'',
$package['source']['url']
);

return sprintf(
'%s/releases/download/%s/%s',
$baseUrl,
$this->remoteVersion,
$this->getPharName()
);
}
}
