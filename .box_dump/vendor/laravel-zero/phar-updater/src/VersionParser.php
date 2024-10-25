<?php











namespace Humbug\SelfUpdate;

class VersionParser
{



private $versions;




private $modifier = '[._-]?(?:(stable|beta|b|RC|alpha|a|patch|pl|p)(?:[.-]?(\d+))?)?([.-]?dev)?';




public function __construct(array $versions = [])
{
$this->versions = $versions;
}







public function getMostRecentStable()
{
return $this->selectRecentStable();
}







public function getMostRecentUnStable()
{
return $this->selectRecentUnstable();
}







public function getMostRecentAll()
{
return $this->selectRecentAll();
}







public function isStable($version)
{
return $this->stable($version);
}








public function isPreRelease($version)
{
return ! $this->stable($version) && ! $this->development($version);
}








public function isUnstable($version)
{
return ! $this->stable($version);
}







public function isDevelopment($version)
{
return $this->development($version);
}

private function selectRecentStable()
{
$candidates = [];
foreach ($this->versions as $version) {
if (! $this->stable($version)) {
continue;
}
$candidates[] = $version;
}
if (empty($candidates)) {
return false;
}

return $this->findMostRecent($candidates);
}

private function selectRecentUnstable()
{
$candidates = [];
foreach ($this->versions as $version) {
if ($this->stable($version) || $this->development($version)) {
continue;
}
$candidates[] = $version;
}
if (empty($candidates)) {
return false;
}

return $this->findMostRecent($candidates);
}

private function selectRecentAll()
{
$candidates = [];
foreach ($this->versions as $version) {
if ($this->development($version)) {
continue;
}
$candidates[] = $version;
}
if (empty($candidates)) {
return false;
}

return $this->findMostRecent($candidates);
}


private function findMostRecent(array $candidates)
{
$candidate = '';
foreach ($candidates as $version) {
if (version_compare($candidate, $version, '<')) {
$candidate = $version;
}
}

return $candidate;
}

private function stable($version)
{
$version = preg_replace('{#.+$}i', '', $version);
if ($this->development($version)) {
return false;
}
preg_match('{'.$this->modifier.'$}i', strtolower($version), $match);
if (! empty($match[3])) {
return false;
}
if (! empty($match[1])) {
if ('beta' === $match[1] || 'b' === $match[1]
|| 'alpha' === $match[1] || 'a' === $match[1]
|| 'rc' === $match[1]) {
return false;
}
}

return true;
}

private function development($version)
{
if ('dev-' === substr($version, 0, 4) || '-dev' === substr($version, -4)) {
return true;
}
if (1 == preg_match("/-\d+-[a-z0-9]{8,}$/", $version)) {
return true;
}

return false;
}
}
