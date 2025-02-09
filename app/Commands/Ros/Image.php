<?php

declare(strict_types=1);

namespace App\Commands\Ros;

use App\Framework\Commands\Command;
use Symfony\Component\Finder\Finder;

class Image extends Command
{
    protected $signature = 'ros:image';
    protected $description = 'Command description';

    public function handle()
    {
        $keyrings = iterator_to_array(Finder::create()
            ->in('/usr/share/keyrings')
            ->notName('-buster-')
            ->notName('-bullseye-'));
        dump($keyrings);
    }
}
