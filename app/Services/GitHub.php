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

    public static function tagLatest(string $input): string
    {
        $input = static::isValidRepo(static::resolveRepository($input), true);

        $url = "https://api.github.com/repos/{$input['org']}/{$input['repo']}/tags";
        dump($url);
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
        ])->get($url);
        dump($response->json());
        return '';
//
//        return $response->json('tag_name');
    }

    public static function releaseLatest(string $input): string
    {
        $input = static::isValidRepo(static::resolveRepository($input), true);

        $url = "https://api.github.com/repos/{$input['org']}/{$input['repo']}/releases/latest";
        dump($url);
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
        ])->get($url);
        dump($response->json());
        return '';
//
//        return $response->json('tag_name');
    }

}
