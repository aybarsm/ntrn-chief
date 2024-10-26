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

    protected static function isValidRepo(array $repo, bool $throw = false): bool|array
    {
        $result = isset($repo['org']) && isset($repo['repo']) && !blank($repo['org']) && !blank($repo['repo']);

        throw_if($throw && ! $result, \InvalidArgumentException::class, 'Invalid repository format.');

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

    public static function apiGet(string $url)
    {
        return Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
        ])->get($url);
    }

    public static function getTags(string $input): \Illuminate\Http\Client\Response
    {
        $input = static::isValidRepo(static::resolveRepository($input), true);
        $url = "https://api.github.com/repos/{$input['org']}/{$input['repo']}/tags";

        return static::apiGet($url);
    }

    public static function tagLatest(string $input): string
    {
        return static::getTags($input)->json('0.name');
    }

    public static function releaseLatest(string $input): string
    {
        $input = static::isValidRepo(static::resolveRepository($input), true);
        $url = "https://api.github.com/repos/{$input['org']}/{$input['repo']}/releases/latest";

        return static::apiGet($url)->json('tag_name');
    }

}
