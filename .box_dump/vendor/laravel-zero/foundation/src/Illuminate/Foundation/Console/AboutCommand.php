<?php

namespace Illuminate\Foundation\Console;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'about')]
class AboutCommand extends Command
{





protected $signature = 'about {--only= : The section to display}
                {--json : Output the information as JSON}';






protected $description = 'Display basic information about your application';






protected $composer;






protected static $data = [];






protected static $customDataResolvers = [];







public function __construct(Composer $composer)
{
parent::__construct();

$this->composer = $composer;
}






public function handle()
{
$this->gatherApplicationInformation();

collect(static::$data)
->map(fn ($items) => collect($items)
->map(function ($value) {
if (is_array($value)) {
return [$value];
}

if (is_string($value)) {
$value = $this->laravel->make($value);
}

return collect($this->laravel->call($value))
->map(fn ($value, $key) => [$key, $value])
->values()
->all();
})->flatten(1)
)
->sortBy(function ($data, $key) {
$index = array_search($key, ['Environment', 'Cache', 'Drivers']);

return $index === false ? 99 : $index;
})
->filter(function ($data, $key) {
return $this->option('only') ? in_array($this->toSearchKeyword($key), $this->sections()) : true;
})
->pipe(fn ($data) => $this->display($data));

$this->newLine();

return 0;
}







protected function display($data)
{
$this->option('json') ? $this->displayJson($data) : $this->displayDetail($data);
}







protected function displayDetail($data)
{
$data->each(function ($data, $section) {
$this->newLine();

$this->components->twoColumnDetail('  <fg=green;options=bold>'.$section.'</>');

$data->pipe(fn ($data) => $section !== 'Environment' ? $data->sort() : $data)->each(function ($detail) {
[$label, $value] = $detail;

$this->components->twoColumnDetail($label, value($value, false));
});
});
}







protected function displayJson($data)
{
$output = $data->flatMap(function ($data, $section) {
return [
(string) Str::of($section)->snake() => $data->mapWithKeys(fn ($item, $key) => [
$this->toSearchKeyword($item[0]) => value($item[1], true),
]),
];
});

$this->output->writeln(strip_tags(json_encode($output)));
}






protected function gatherApplicationInformation()
{
self::$data = [];

$formatEnabledStatus = fn ($value) => $value ? '<fg=yellow;options=bold>ENABLED</>' : 'OFF';
$formatCachedStatus = fn ($value) => $value ? '<fg=green;options=bold>CACHED</>' : '<fg=yellow;options=bold>NOT CACHED</>';

static::addToSection('Environment', fn () => [
'Application Name' => config('app.name'),
'Laravel Version' => $this->laravel->version(),
'PHP Version' => phpversion(),
'Composer Version' => $this->composer->getVersion() ?? '<fg=yellow;options=bold>-</>',
'Environment' => $this->laravel->environment(),
'Debug Mode' => static::format(config('app.debug'), console: $formatEnabledStatus),
'URL' => Str::of(config('app.url'))->replace(['http://', 'https://'], ''),
'Maintenance Mode' => static::format($this->laravel->isDownForMaintenance(), console: $formatEnabledStatus),
]);

static::addToSection('Cache', fn () => [
'Config' => static::format($this->laravel->configurationIsCached(), console: $formatCachedStatus),
'Events' => static::format($this->laravel->eventsAreCached(), console: $formatCachedStatus),
'Routes' => static::format($this->laravel->routesAreCached(), console: $formatCachedStatus),
'Views' => static::format($this->hasPhpFiles($this->laravel->storagePath('framework/views')), console: $formatCachedStatus),
]);

static::addToSection('Drivers', fn () => array_filter([
'Broadcasting' => config('broadcasting.default'),
'Cache' => config('cache.default'),
'Database' => config('database.default'),
'Logs' => function ($json) {
$logChannel = config('logging.default');

if (config('logging.channels.'.$logChannel.'.driver') === 'stack') {
$secondary = collect(config('logging.channels.'.$logChannel.'.channels'));

return value(static::format(
value: $logChannel,
console: fn ($value) => '<fg=yellow;options=bold>'.$value.'</> <fg=gray;options=bold>/</> '.$secondary->implode(', '),
json: fn () => $secondary->all(),
), $json);
} else {
$logs = $logChannel;
}

return $logs;
},
'Mail' => config('mail.default'),
'Octane' => config('octane.server'),
'Queue' => config('queue.default'),
'Scout' => config('scout.driver'),
'Session' => config('session.driver'),
]));

collect(static::$customDataResolvers)->each->__invoke();
}







protected function hasPhpFiles(string $path): bool
{
return count(glob($path.'/*.php')) > 0;
}









public static function add(string $section, $data, ?string $value = null)
{
static::$customDataResolvers[] = fn () => static::addToSection($section, $data, $value);
}









protected static function addToSection(string $section, $data, ?string $value = null)
{
if (is_array($data)) {
foreach ($data as $key => $value) {
self::$data[$section][] = [$key, $value];
}
} elseif (is_callable($data) || ($value === null && class_exists($data))) {
self::$data[$section][] = $data;
} else {
self::$data[$section][] = [$data, $value];
}
}






protected function sections()
{
return collect(explode(',', $this->option('only') ?? ''))
->filter()
->map(fn ($only) => $this->toSearchKeyword($only))
->all();
}









public static function format($value, ?Closure $console = null, ?Closure $json = null)
{
return function ($isJson) use ($value, $console, $json) {
if ($isJson === true && $json instanceof Closure) {
return value($json, $value);
} elseif ($isJson === false && $console instanceof Closure) {
return value($console, $value);
}

return value($value);
};
}







protected function toSearchKeyword(string $value)
{
return (string) Str::of($value)->lower()->snake();
}






public static function flushState()
{
static::$data = [];

static::$customDataResolvers = [];
}
}
