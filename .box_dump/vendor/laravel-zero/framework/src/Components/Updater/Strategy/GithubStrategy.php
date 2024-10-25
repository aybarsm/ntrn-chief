<?php

namespace LaravelZero\Framework\Components\Updater\Strategy;

use Phar;

final class GithubStrategy extends \Humbug\SelfUpdate\Strategy\GithubStrategy implements StrategyInterface
{



protected function getDownloadUrl(array $package): string
{
$downloadUrl = parent::getDownloadUrl($package);

$downloadUrl = str_replace('releases/download', 'raw', $downloadUrl);

return $downloadUrl.'/builds/'.basename(Phar::running());
}
}
