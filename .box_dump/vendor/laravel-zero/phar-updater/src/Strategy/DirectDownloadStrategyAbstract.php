<?php

namespace Humbug\SelfUpdate\Strategy;

use Humbug\SelfUpdate\Exception\HttpRequestException;
use Humbug\SelfUpdate\Updater;

abstract class DirectDownloadStrategyAbstract implements StrategyInterface
{
protected string $localVersion;

abstract public function getDownloadUrl(): string;


public function download(Updater $updater)
{

set_error_handler([$updater, 'throwHttpRequestException']);
$result = file_get_contents($this->getDownloadUrl());
restore_error_handler();
if (false === $result) {
throw new HttpRequestException(sprintf(
'Request to URL failed: %s',
$this->getDownloadUrl()
));
}

file_put_contents($updater->getTempPharFile(), $result);
}


public function getCurrentRemoteVersion(Updater $updater)
{
return 'latest';
}

public function setCurrentLocalVersion(string $version): void
{
$this->localVersion = $version;
}


public function getCurrentLocalVersion(Updater $updater)
{
return $this->localVersion;
}
}
