<?php

declare(strict_types=1);

namespace App\Commands\Ros;

use App\Framework\Commands\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Image extends Command
{
    protected $signature = 'ros:image';
    protected $description = 'Command description';

    public function handle()
    {


        $copy['exact'] = [
            '/etc/apt/sources.list.d/debian.sources'
        ];

        $copy['exact'] = array_merge($copy['exact'], $this->splIteratorToArray(Finder::create()
            ->in('/usr/share/keyrings')
            ->notName('-buster-')
            ->notName('-bullseye-')));

        $copy['exact'] = array_merge($copy['exact'], $this->splIteratorToArray(Finder::create()
            ->in('/etc/apt/trusted.gpg.d')
            ->notName('-buster-')
            ->notName('-bullseye-')));

        dump($copy['exact']);
        print_r($copy['exact']);
    }

    protected function splIteratorToArray($finder)
    {
        return array_values(Arr::map(iterator_to_array($finder), fn (SplFileInfo $file) => $file->getRealPath()));
    }
}
