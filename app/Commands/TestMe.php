<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use function Illuminate\Filesystem\join_paths;

class TestMe extends Command
{
    protected $signature = 'test:me';
    protected $description = 'Command description';

    public function handle(): void
    {
        $temp = join_paths(sys_get_temp_dir(), 'testme');
        $this->info($temp);
//        $temp = tmpfile();
//        $this->info('Temp : ' . $temp);
//        $this->info(sys_get_temp_dir());
//        $file = $this->app->buildsPath('v0.0.1-20241025T182405Z/deneme');
//        $this->info('File : ' . $file . ' : ' . fileperms($file));
//        dump(stat($file));
//        dump(fileperms($file));
//        $file = $this->app->buildsPath('v0.0.1-20241025T182405Z/ntrn-linux-x86_64');
//        $this->info('File : ' . $file . ' : ' . fileperms($file));
//        $file = $this->app->buildsPath('v0.0.1-20241025T182405Z/denemex');
//        $this->info('File : ' . $file . ' : ' . fileperms($file));
//        $file = $this->app->buildsPath('v0.0.1-20241025T182405Z/denemez');
//        $this->info('File : ' . $file . ' : ' . fileperms($file));
//        $file = $this->app->buildsPath('v0.0.1-20241025T182405Z/denemey');
//        File::chmod($file, octdec(config('dev.build.chmod')));
//        $this->info('File : ' . $file . ' : ' . fileperms($file));
//        dump(stat($file));
//        dump(fileperms($file));
//        $this->info(octdec('0775'));
    }

}
