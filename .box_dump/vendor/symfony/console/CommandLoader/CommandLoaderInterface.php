<?php










namespace Symfony\Component\Console\CommandLoader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;




interface CommandLoaderInterface
{





public function get(string $name): Command;




public function has(string $name): bool;




public function getNames(): array;
}
