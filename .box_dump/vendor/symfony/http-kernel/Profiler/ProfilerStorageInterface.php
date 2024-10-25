<?php










namespace Symfony\Component\HttpKernel\Profiler;














interface ProfilerStorageInterface
{









public function find(?string $ip, ?string $url, ?int $limit, ?string $method, ?int $start = null, ?int $end = null, ?string $statusCode = null, ?\Closure $filter = null): array;






public function read(string $token): ?Profile;




public function write(Profile $profile): bool;




public function purge(): void;
}
