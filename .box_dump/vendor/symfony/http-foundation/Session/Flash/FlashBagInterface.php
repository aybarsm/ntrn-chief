<?php










namespace Symfony\Component\HttpFoundation\Session\Flash;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;






interface FlashBagInterface extends SessionBagInterface
{



public function add(string $type, mixed $message): void;




public function set(string $type, string|array $messages): void;







public function peek(string $type, array $default = []): array;




public function peekAll(): array;






public function get(string $type, array $default = []): array;




public function all(): array;




public function setAll(array $messages): void;




public function has(string $type): bool;




public function keys(): array;
}
