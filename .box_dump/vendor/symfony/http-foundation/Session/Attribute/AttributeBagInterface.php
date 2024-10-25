<?php










namespace Symfony\Component\HttpFoundation\Session\Attribute;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;






interface AttributeBagInterface extends SessionBagInterface
{



public function has(string $name): bool;




public function get(string $name, mixed $default = null): mixed;




public function set(string $name, mixed $value): void;






public function all(): array;

public function replace(array $attributes): void;






public function remove(string $name): mixed;
}
