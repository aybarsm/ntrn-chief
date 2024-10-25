<?php










namespace Symfony\Component\Process;







class ExecutableFinder
{
private array $suffixes = ['.exe', '.bat', '.cmd', '.com'];




public function setSuffixes(array $suffixes): void
{
$this->suffixes = $suffixes;
}




public function addSuffix(string $suffix): void
{
$this->suffixes[] = $suffix;
}








public function find(string $name, ?string $default = null, array $extraDirs = []): ?string
{
$dirs = array_merge(
explode(\PATH_SEPARATOR, getenv('PATH') ?: getenv('Path')),
$extraDirs
);

$suffixes = [''];
if ('\\' === \DIRECTORY_SEPARATOR) {
$pathExt = getenv('PATHEXT');
$suffixes = array_merge($pathExt ? explode(\PATH_SEPARATOR, $pathExt) : $this->suffixes, $suffixes);
}
foreach ($suffixes as $suffix) {
foreach ($dirs as $dir) {
if (@is_file($file = $dir.\DIRECTORY_SEPARATOR.$name.$suffix) && ('\\' === \DIRECTORY_SEPARATOR || @is_executable($file))) {
return $file;
}

if (!@is_dir($dir) && basename($dir) === $name.$suffix && @is_executable($dir)) {
return $dir;
}
}
}

$command = '\\' === \DIRECTORY_SEPARATOR ? 'where' : 'command -v --';
if (\function_exists('exec') && ($executablePath = strtok(@exec($command.' '.escapeshellarg($name)), \PHP_EOL)) && @is_executable($executablePath)) {
return $executablePath;
}

return $default;
}
}
