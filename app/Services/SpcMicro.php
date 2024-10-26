<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use function Illuminate\Filesystem\join_paths;

class SpcMicro
{
    public function __construct(
        protected string $url,
        protected string $path,
    )
    {

    }

    public function download(bool $overwrite = false, array $clientConfig = []): static
    {
        if (File::exists($this->path) && ! $overwrite) {
            return $this;
        }

        $download = join_paths(sys_get_temp_dir(), basename($this->url));
        $client = Http::sink($download);

        foreach($clientConfig as $method => $parameters) {
            if (in_array($method, ['sink', 'get', 'post', 'put', 'patch', 'delete', 'send'])) {
                continue;
            }
            if (method_exists($client, $method)) {
                $client = $client->{$method}(...$parameters);
            }
        }

        return $this;
    }
}
