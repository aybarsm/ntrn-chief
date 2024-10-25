<?php

namespace Illuminate\Foundation\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:decrypt')]
class EnvironmentDecryptCommand extends Command
{





protected $signature = 'env:decrypt
                    {--key= : The encryption key}
                    {--cipher= : The encryption cipher}
                    {--env= : The environment to be decrypted}
                    {--force : Overwrite the existing environment file}
                    {--path= : Path to write the decrypted file}
                    {--filename= : Filename of the decrypted file}';






protected $description = 'Decrypt an environment file';






protected $files;







public function __construct(Filesystem $files)
{
parent::__construct();

$this->files = $files;
}






public function handle()
{
$key = $this->option('key') ?: Env::get('LARAVEL_ENV_ENCRYPTION_KEY');

if (! $key) {
$this->components->error('A decryption key is required.');

return Command::FAILURE;
}

$cipher = $this->option('cipher') ?: 'AES-256-CBC';

$key = $this->parseKey($key);

$encryptedFile = ($this->option('env')
? Str::finish(dirname($this->laravel->environmentFilePath()), DIRECTORY_SEPARATOR).'.env.'.$this->option('env')
: $this->laravel->environmentFilePath()).'.encrypted';

$outputFile = $this->outputFilePath();

if (Str::endsWith($outputFile, '.encrypted')) {
$this->components->error('Invalid filename.');

return Command::FAILURE;
}

if (! $this->files->exists($encryptedFile)) {
$this->components->error('Encrypted environment file not found.');

return Command::FAILURE;
}

if ($this->files->exists($outputFile) && ! $this->option('force')) {
$this->components->error('Environment file already exists.');

return Command::FAILURE;
}

try {
$encrypter = new Encrypter($key, $cipher);

$this->files->put(
$outputFile,
$encrypter->decrypt($this->files->get($encryptedFile))
);
} catch (Exception $e) {
$this->components->error($e->getMessage());

return Command::FAILURE;
}

$this->components->info('Environment successfully decrypted.');

$this->components->twoColumnDetail('Decrypted file', $outputFile);

$this->newLine();
}







protected function parseKey(string $key)
{
if (Str::startsWith($key, $prefix = 'base64:')) {
$key = base64_decode(Str::after($key, $prefix));
}

return $key;
}






protected function outputFilePath()
{
$path = Str::finish($this->option('path') ?: dirname($this->laravel->environmentFilePath()), DIRECTORY_SEPARATOR);

$outputFile = $this->option('filename') ?: ('.env'.($this->option('env') ? '.'.$this->option('env') : ''));
$outputFile = ltrim($outputFile, DIRECTORY_SEPARATOR);

return $path.$outputFile;
}
}
