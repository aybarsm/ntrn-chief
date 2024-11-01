<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GitHub
{
    public static function resolveRepository(string $input): array
    {
        $input=trim($input);

        return match(true){
            Str::isUrl($input) => static::resolveRepositoryFromUrl($input),
            Str::isMatch($input, '/^(?<org>[^\/]+)\/(?<repo>[^\/]+)$/') => static::resolveRepositoryDirect($input),
        };
    }

    protected static function isValidRepo(array $repo, string $input, bool $throw = false): bool|array
    {
        $result = isset($repo['org']) && isset($repo['repo']) && ! blank($repo['org']) && ! blank($repo['repo']);

        throw_if($throw && ! $result, \InvalidArgumentException::class, "Invalid [{$input}] repository format.");

        return $throw ? $repo : $result;
    }

    protected static function handleMatches(array $matches): array
    {
        return Arr::only($matches, ['org', 'repo']);
    }

    public static function resolveRepositoryDirect(string $full): array
    {
        preg_match('/^(?<org>[^\/]+)\/(?<repo>[^\/]+)$/', $full, $matches);

        return static::handleMatches($matches);
    }
    public static function resolveRepositoryFromUrl(string $url): array
    {
        preg_match('/github\.com\/(?<org>[^\/]+)\/(?<repo>[^\/]+)/', $url, $matches);

        return static::handleMatches($matches);
    }

    public static function apiGet(string $org, string $repo, string $path): array
    {
        $url = "https://api.github.com/repos/{$org}/{$repo}/{$path}";

        $request = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
        ]);

        if (! blank(config('dev.github.token'))) {
            $request->withToken(config('dev.github.token'));
        }

        return $request->get($url)->json();
    }

    public static function getTags(string $input): array
    {
        $input = static::isValidRepo(static::resolveRepository($input), $input, true);

        return static::apiGet($input['org'], $input['repo'], 'tags');
    }

    public static function tagLatest(string $input, mixed $default = null): mixed
    {
        $input = static::isValidRepo(static::resolveRepository($input), $input, true);

        $tags = static::getTags($input);
        return blank($tags) ? $default : Arr::first($tags);
    }

    public static function getReleases(string $input): array
    {
        $input = static::isValidRepo(static::resolveRepository($input), $input, true);

        return static::apiGet($input['org'], $input['repo'], 'releases');
    }

    public static function releaseLatest(string $input, mixed $default = null): mixed
    {
        $input = static::isValidRepo(static::resolveRepository($input), $input, true);

        if (! blank(static::getReleases($input))){
            return static::apiGet($input['org'], $input['repo'], 'releases/latest');
        }

        return $default;
    }

}
