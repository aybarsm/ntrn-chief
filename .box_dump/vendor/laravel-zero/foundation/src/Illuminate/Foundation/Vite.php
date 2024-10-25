<?php

namespace Illuminate\Foundation;

use Exception;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class Vite implements Htmlable
{
use Macroable;






protected $nonce;






protected $integrityKey = 'integrity';






protected $entryPoints = [];






protected $hotFile;






protected $buildDirectory = 'build';






protected $manifestFilename = 'manifest.json';






protected $assetPathResolver = null;






protected $scriptTagAttributesResolvers = [];






protected $styleTagAttributesResolvers = [];






protected $preloadTagAttributesResolvers = [];






protected $preloadedAssets = [];






protected static $manifests = [];






public function preloadedAssets()
{
return $this->preloadedAssets;
}






public function cspNonce()
{
return $this->nonce;
}







public function useCspNonce($nonce = null)
{
return $this->nonce = $nonce ?? Str::random(40);
}







public function useIntegrityKey($key)
{
$this->integrityKey = $key;

return $this;
}







public function withEntryPoints($entryPoints)
{
$this->entryPoints = $entryPoints;

return $this;
}







public function useManifestFilename($filename)
{
$this->manifestFilename = $filename;

return $this;
}







public function createAssetPathsUsing($resolver)
{
$this->assetPathResolver = $resolver;

return $this;
}






public function hotFile()
{
return $this->hotFile ?? public_path('/hot');
}







public function useHotFile($path)
{
$this->hotFile = $path;

return $this;
}







public function useBuildDirectory($path)
{
$this->buildDirectory = $path;

return $this;
}







public function useScriptTagAttributes($attributes)
{
if (! is_callable($attributes)) {
$attributes = fn () => $attributes;
}

$this->scriptTagAttributesResolvers[] = $attributes;

return $this;
}







public function useStyleTagAttributes($attributes)
{
if (! is_callable($attributes)) {
$attributes = fn () => $attributes;
}

$this->styleTagAttributesResolvers[] = $attributes;

return $this;
}







public function usePreloadTagAttributes($attributes)
{
if (! is_callable($attributes)) {
$attributes = fn () => $attributes;
}

$this->preloadTagAttributesResolvers[] = $attributes;

return $this;
}










public function __invoke($entrypoints, $buildDirectory = null)
{
$entrypoints = collect($entrypoints);
$buildDirectory ??= $this->buildDirectory;

if ($this->isRunningHot()) {
return new HtmlString(
$entrypoints
->prepend('@vite/client')
->map(fn ($entrypoint) => $this->makeTagForChunk($entrypoint, $this->hotAsset($entrypoint), null, null))
->join('')
);
}

$manifest = $this->manifest($buildDirectory);

$tags = collect();
$preloads = collect();

foreach ($entrypoints as $entrypoint) {
$chunk = $this->chunk($manifest, $entrypoint);

$preloads->push([
$chunk['src'],
$this->assetPath("{$buildDirectory}/{$chunk['file']}"),
$chunk,
$manifest,
]);

foreach ($chunk['imports'] ?? [] as $import) {
$preloads->push([
$import,
$this->assetPath("{$buildDirectory}/{$manifest[$import]['file']}"),
$manifest[$import],
$manifest,
]);

foreach ($manifest[$import]['css'] ?? [] as $css) {
$partialManifest = Collection::make($manifest)->where('file', $css);

$preloads->push([
$partialManifest->keys()->first(),
$this->assetPath("{$buildDirectory}/{$css}"),
$partialManifest->first(),
$manifest,
]);

$tags->push($this->makeTagForChunk(
$partialManifest->keys()->first(),
$this->assetPath("{$buildDirectory}/{$css}"),
$partialManifest->first(),
$manifest
));
}
}

$tags->push($this->makeTagForChunk(
$entrypoint,
$this->assetPath("{$buildDirectory}/{$chunk['file']}"),
$chunk,
$manifest
));

foreach ($chunk['css'] ?? [] as $css) {
$partialManifest = Collection::make($manifest)->where('file', $css);

$preloads->push([
$partialManifest->keys()->first(),
$this->assetPath("{$buildDirectory}/{$css}"),
$partialManifest->first(),
$manifest,
]);

$tags->push($this->makeTagForChunk(
$partialManifest->keys()->first(),
$this->assetPath("{$buildDirectory}/{$css}"),
$partialManifest->first(),
$manifest
));
}
}

[$stylesheets, $scripts] = $tags->unique()->partition(fn ($tag) => str_starts_with($tag, '<link'));

$preloads = $preloads->unique()
->sortByDesc(fn ($args) => $this->isCssPath($args[1]))
->map(fn ($args) => $this->makePreloadTagForChunk(...$args));

return new HtmlString($preloads->join('').$stylesheets->join('').$scripts->join(''));
}










protected function makeTagForChunk($src, $url, $chunk, $manifest)
{
if (
$this->nonce === null
&& $this->integrityKey !== false
&& ! array_key_exists($this->integrityKey, $chunk ?? [])
&& $this->scriptTagAttributesResolvers === []
&& $this->styleTagAttributesResolvers === []) {
return $this->makeTag($url);
}

if ($this->isCssPath($url)) {
return $this->makeStylesheetTagWithAttributes(
$url,
$this->resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)
);
}

return $this->makeScriptTagWithAttributes(
$url,
$this->resolveScriptTagAttributes($src, $url, $chunk, $manifest)
);
}










protected function makePreloadTagForChunk($src, $url, $chunk, $manifest)
{
$attributes = $this->resolvePreloadTagAttributes($src, $url, $chunk, $manifest);

if ($attributes === false) {
return '';
}

$this->preloadedAssets[$url] = $this->parseAttributes(
Collection::make($attributes)->forget('href')->all()
);

return '<link '.implode(' ', $this->parseAttributes($attributes)).' />';
}










protected function resolveScriptTagAttributes($src, $url, $chunk, $manifest)
{
$attributes = $this->integrityKey !== false
? ['integrity' => $chunk[$this->integrityKey] ?? false]
: [];

foreach ($this->scriptTagAttributesResolvers as $resolver) {
$attributes = array_merge($attributes, $resolver($src, $url, $chunk, $manifest));
}

return $attributes;
}










protected function resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)
{
$attributes = $this->integrityKey !== false
? ['integrity' => $chunk[$this->integrityKey] ?? false]
: [];

foreach ($this->styleTagAttributesResolvers as $resolver) {
$attributes = array_merge($attributes, $resolver($src, $url, $chunk, $manifest));
}

return $attributes;
}










protected function resolvePreloadTagAttributes($src, $url, $chunk, $manifest)
{
$attributes = $this->isCssPath($url) ? [
'rel' => 'preload',
'as' => 'style',
'href' => $url,
'nonce' => $this->nonce ?? false,
'crossorigin' => $this->resolveStylesheetTagAttributes($src, $url, $chunk, $manifest)['crossorigin'] ?? false,
] : [
'rel' => 'modulepreload',
'href' => $url,
'nonce' => $this->nonce ?? false,
'crossorigin' => $this->resolveScriptTagAttributes($src, $url, $chunk, $manifest)['crossorigin'] ?? false,
];

$attributes = $this->integrityKey !== false
? array_merge($attributes, ['integrity' => $chunk[$this->integrityKey] ?? false])
: $attributes;

foreach ($this->preloadTagAttributesResolvers as $resolver) {
if (false === ($resolvedAttributes = $resolver($src, $url, $chunk, $manifest))) {
return false;
}

$attributes = array_merge($attributes, $resolvedAttributes);
}

return $attributes;
}









protected function makeTag($url)
{
if ($this->isCssPath($url)) {
return $this->makeStylesheetTag($url);
}

return $this->makeScriptTag($url);
}









protected function makeScriptTag($url)
{
return $this->makeScriptTagWithAttributes($url, []);
}









protected function makeStylesheetTag($url)
{
return $this->makeStylesheetTagWithAttributes($url, []);
}








protected function makeScriptTagWithAttributes($url, $attributes)
{
$attributes = $this->parseAttributes(array_merge([
'type' => 'module',
'src' => $url,
'nonce' => $this->nonce ?? false,
], $attributes));

return '<script '.implode(' ', $attributes).'></script>';
}








protected function makeStylesheetTagWithAttributes($url, $attributes)
{
$attributes = $this->parseAttributes(array_merge([
'rel' => 'stylesheet',
'href' => $url,
'nonce' => $this->nonce ?? false,
], $attributes));

return '<link '.implode(' ', $attributes).' />';
}







protected function isCssPath($path)
{
return preg_match('/\.(css|less|sass|scss|styl|stylus|pcss|postcss)$/', $path) === 1;
}







protected function parseAttributes($attributes)
{
return Collection::make($attributes)
->reject(fn ($value, $key) => in_array($value, [false, null], true))
->flatMap(fn ($value, $key) => $value === true ? [$key] : [$key => $value])
->map(fn ($value, $key) => is_int($key) ? $value : $key.'="'.$value.'"')
->values()
->all();
}






public function reactRefresh()
{
if (! $this->isRunningHot()) {
return;
}

$attributes = $this->parseAttributes([
'nonce' => $this->cspNonce(),
]);

return new HtmlString(
sprintf(
<<<'HTML'
                <script type="module" %s>
                    import RefreshRuntime from '%s'
                    RefreshRuntime.injectIntoGlobalHook(window)
                    window.$RefreshReg$ = () => {}
                    window.$RefreshSig$ = () => (type) => type
                    window.__vite_plugin_react_preamble_installed__ = true
                </script>
                HTML,
implode(' ', $attributes),
$this->hotAsset('@react-refresh')
)
);
}






protected function hotAsset($asset)
{
return rtrim(file_get_contents($this->hotFile())).'/'.$asset;
}








public function asset($asset, $buildDirectory = null)
{
$buildDirectory ??= $this->buildDirectory;

if ($this->isRunningHot()) {
return $this->hotAsset($asset);
}

$chunk = $this->chunk($this->manifest($buildDirectory), $asset);

return $this->assetPath($buildDirectory.'/'.$chunk['file']);
}










public function content($asset, $buildDirectory = null)
{
$buildDirectory ??= $this->buildDirectory;

$chunk = $this->chunk($this->manifest($buildDirectory), $asset);

$path = public_path($buildDirectory.'/'.$chunk['file']);

if (! is_file($path) || ! file_exists($path)) {
throw new Exception("Unable to locate file from Vite manifest: {$path}.");
}

return file_get_contents($path);
}








protected function assetPath($path, $secure = null)
{
return ($this->assetPathResolver ?? asset(...))($path, $secure);
}









protected function manifest($buildDirectory)
{
$path = $this->manifestPath($buildDirectory);

if (! isset(static::$manifests[$path])) {
if (! is_file($path)) {
throw new ViteManifestNotFoundException("Vite manifest not found at: $path");
}

static::$manifests[$path] = json_decode(file_get_contents($path), true);
}

return static::$manifests[$path];
}







protected function manifestPath($buildDirectory)
{
return public_path($buildDirectory.'/'.$this->manifestFilename);
}







public function manifestHash($buildDirectory = null)
{
$buildDirectory ??= $this->buildDirectory;

if ($this->isRunningHot()) {
return null;
}

if (! is_file($path = $this->manifestPath($buildDirectory))) {
return null;
}

return md5_file($path) ?: null;
}










protected function chunk($manifest, $file)
{
if (! isset($manifest[$file])) {
throw new Exception("Unable to locate file in Vite manifest: {$file}.");
}

return $manifest[$file];
}






public function isRunningHot()
{
return is_file($this->hotFile());
}






public function toHtml()
{
return $this->__invoke($this->entryPoints)->toHtml();
}
}
