<?php

declare(strict_types=1);










namespace LaravelZero\Framework\Components\Updater;

use Humbug\SelfUpdate\Updater as PharUpdater;
use LaravelZero\Framework\Components\AbstractComponentProvider;
use LaravelZero\Framework\Components\Updater\Strategy\GithubStrategy;
use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;
use LaravelZero\Framework\Providers\Build\Build;

use function class_exists;




final class Provider extends AbstractComponentProvider
{



public function isAvailable(): bool
{
return class_exists(\Humbug\SelfUpdate\Updater::class);
}




public function boot(): void
{
$build = $this->app->make(Build::class);

if (! $this->app->environment('production')) {
$this->publishes([
__DIR__.'/config/updater.php' => $this->app->configPath('updater.php'),
]);
}

$this->mergeConfigFrom(__DIR__.'/config/updater.php', 'updater');

if ($build->isRunning() && $this->app->environment() === 'production') {
$this->commands([
SelfUpdateCommand::class,
]);
}
}




public function register(): void
{
$build = $this->app->make(Build::class);

if ($build->isRunning() && $this->app->environment('production')) {
$this->app->singleton(Updater::class, function () use ($build) {
$updater = new PharUpdater($build->getPath(), false, PharUpdater::STRATEGY_GITHUB);

$composer = json_decode(file_get_contents(base_path('composer.json')), true);
$name = $composer['name'];

$strategy = $this->app['config']->get('updater.strategy', GithubStrategy::class);

$updater->setStrategyObject($this->app->make($strategy));

if ($updater->getStrategy() instanceof StrategyInterface) {
$updater->getStrategy()->setPackageName($name);
}

if (method_exists($updater->getStrategy(), 'setCurrentLocalVersion')) {
$updater->getStrategy()->setCurrentLocalVersion(config('app.version'));
}

return new Updater($updater);
});
}
}
}
