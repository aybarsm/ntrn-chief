<?php

namespace Illuminate\Foundation\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'env:encrypt')]
class EnvironmentEncryptCommand extends Command
{





protected $signature = 'env:encrypt
                    {--key= : The encryption key}
                    {--cipher= : The encryption cipher}
                    {--env= : The environment to be encrypted}
                    {--force : Overwrite the existing encrypted environment file}';






protected $description = 'Encrypt an environment file';






protected $files;







public function __construct(Filesystem $files)
{
parent::__construct();

$this->files = $files;
}






public function handle()
{
$cipher = $this->option('cipher') ?: 'AES-256-CBC';

$key = $this->option('key');

$keyPassed = $key !== null;

$environmentFile = $this->option('env')
? Str::finish(dirname($this->laravel->environmentFilePath()), DIRECTORY_SEPARATOR).'.env.'.$this->option('env')
: $this->laravel->environmentFilePath();

$encryptedFile = $environmentFile.'.encrypted';

if (! $keyPassed) {
$key = Encrypter::generateKey($cipher);
}

if (! $this->files->exists($environmentFile)) {
$this->components->error('Environment file not found.');

return Command::FAILURE;
}

if ($this->files->exists($encryptedFile) && ! $this->option('force')) {
$this->components->error('Encrypted environment file already exists.');

return Command::FAILURE;
}

try {
$encrypter = new Encrypter($this->parseKey($key), $cipher);

$this->files->put(
$encryptedFile,
$encrypter->encrypt($this->files->get($environmentFile))
);
} catch (Exception $e) {
$this->components->error($e->getMessage());

return Command::FAILURE;
}

$this->components->info('Environment successfully encrypted.');

$this->components->twoColumnDetail('Key', $keyPassed ? $key : 'base64:'.base64_encode($key));
$this->components->twoColumnDetail('Cipher', $cipher);
$this->components->twoColumnDetail('Encrypted file', $encryptedFile);

$this->newLine();
}







protected function parseKey(string $key)
{
if (Str::startsWith($key, $prefix = 'base64:')) {
$key = base64_decode(Str::after($key, $prefix));
}

return $key;
}
}
