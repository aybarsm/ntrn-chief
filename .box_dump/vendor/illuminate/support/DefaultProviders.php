<?php

namespace Illuminate\Support;

class DefaultProviders
{





protected $providers;






public function __construct(?array $providers = null)
{
$this->providers = $providers ?: [
\Illuminate\Auth\AuthServiceProvider::class,
\Illuminate\Broadcasting\BroadcastServiceProvider::class,
\Illuminate\Bus\BusServiceProvider::class,
\Illuminate\Cache\CacheServiceProvider::class,
\Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
\Illuminate\Concurrency\ConcurrencyServiceProvider::class,
\Illuminate\Cookie\CookieServiceProvider::class,
\Illuminate\Database\DatabaseServiceProvider::class,
\Illuminate\Encryption\EncryptionServiceProvider::class,
\Illuminate\Filesystem\FilesystemServiceProvider::class,
\Illuminate\Foundation\Providers\FoundationServiceProvider::class,
\Illuminate\Hashing\HashServiceProvider::class,
\Illuminate\Mail\MailServiceProvider::class,
\Illuminate\Notifications\NotificationServiceProvider::class,
\Illuminate\Pagination\PaginationServiceProvider::class,
\Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
\Illuminate\Pipeline\PipelineServiceProvider::class,
\Illuminate\Queue\QueueServiceProvider::class,
\Illuminate\Redis\RedisServiceProvider::class,
\Illuminate\Session\SessionServiceProvider::class,
\Illuminate\Translation\TranslationServiceProvider::class,
\Illuminate\Validation\ValidationServiceProvider::class,
\Illuminate\View\ViewServiceProvider::class,
];
}







public function merge(array $providers)
{
$this->providers = array_merge($this->providers, $providers);

return new static($this->providers);
}







public function replace(array $replacements)
{
$current = collect($this->providers);

foreach ($replacements as $from => $to) {
$key = $current->search($from);

$current = is_int($key) ? $current->replace([$key => $to]) : $current;
}

return new static($current->values()->toArray());
}







public function except(array $providers)
{
return new static(collect($this->providers)
->reject(fn ($p) => in_array($p, $providers))
->values()
->toArray());
}






public function toArray()
{
return $this->providers;
}
}
