<?php











namespace Humbug\SelfUpdate\Strategy;

use Humbug\SelfUpdate\Updater;

interface StrategyInterface
{





public function download(Updater $updater);






public function getCurrentRemoteVersion(Updater $updater);






public function getCurrentLocalVersion(Updater $updater);
}
