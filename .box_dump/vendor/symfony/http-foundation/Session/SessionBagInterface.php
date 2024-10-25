<?php










namespace Symfony\Component\HttpFoundation\Session;






interface SessionBagInterface
{



public function getName(): string;




public function initialize(array &$array): void;




public function getStorageKey(): string;






public function clear(): mixed;
}
