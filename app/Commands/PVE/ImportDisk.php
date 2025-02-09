<?php

declare(strict_types=1);

namespace App\Commands\PVE;

use App\Framework\Commands\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputOption;
use function Laravel\Prompts\select;
class ImportDisk extends Command implements PromptsForMissingInput
{
    protected $signature = 'pve:import-disk';
    protected $description = 'Proxmox Import Disk';

    public function handle()
    {
        dump($this->arguments());
    }

    public function getArguments(): array
    {
        return [
            ['image', InputArgument::REQUIRED, 'The name of the command'],
            ['disk', InputArgument::REQUIRED, 'Disk No', 0],
        ];
    }

    public function getOptions(): array
    {
        return [
            ['format', null, InputOption::VALUE_REQUIRED, 'The disk format', 'raw', ['raw', 'qcow2', 'vmdk']],
            ['agent', null, InputOption::VALUE_OPTIONAL, 'QEMU Agent', null],
        ];
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        $images = iterator_to_array(Finder::create()->in('/var/lib/vz/images')->path('/\.(qcow2|raw|img)$/')->sortByName());

        return [
            'image' => fn () => select(
                label: 'Select Image',
                options: $images,
                default: 0,
                required: true
            ),
        ];
    }
}
