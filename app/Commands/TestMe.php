<?php

namespace App\Commands;

use App\Services\Archive;
use App\Traits\Command\OriginalOutput;
use App\Traits\Command\SignalHandler;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Process\Pipe;
use Illuminate\Support\Facades\Process;
use Rahul900Day\LaravelConsoleSpinner\Spinner;
use function Illuminate\Filesystem\join_paths;

class TestMe extends Command
{
    use OriginalOutput, SignalHandler;
    protected $signature = 'test:me';
    protected $description = 'Command description';

    public function handle(): void
    {
       $archive = '/Users/aybarsm/Downloads/php-8.3.9-minimal-micro-linux-x86_64.tar.gz';

        $pattern = windows_os() ? '/\\\\(?<fileName>[^\\\\]+)\.(zip|tar|tar.gz)$/' : '/\/(?<fileName>[^\/]+)\.(zip|tar|tar.gz)$/';
        $fileName = Str::of($archive)->match($pattern)->finish('.sfx')->value();
        dump($fileName);
//        $fileName = Str::match($pattern, $archive);
//        $tempDir = join_paths(sys_get_temp_dir(), $fileName);
//        $tempFile = join_paths($tempDir, 'micro.sfx');
//        dump($tempDir);
//        $result = Archive::extractTo($archive, $tempDir, 'micro.sfx', true);
//        dump($result);
//        if (File::exists($tempFile)){
//            $this->info('File exists');
//            File::deleteDirectory($tempDir);
//        }
//       dump(Str::match('/\/(?<fileName>[^\/]+)\.(zip|tar|tar.gz)$/', $archive));
//       $temp = join_paths(sys_get_temp_dir())

//       $file = new \SplFileInfo($archive);
//       dump($file->getBasename($file->getExtension()));
//       dump(basename($archive, '.tar.gz'));


    }

}
