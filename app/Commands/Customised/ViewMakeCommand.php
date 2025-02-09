<?php

namespace App\Commands\Customised;

use Illuminate\Foundation\Console\ViewMakeCommand as BaseViewMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class ViewMakeCommand extends BaseViewMakeCommand
{
    protected function getPath($name): string
    {
        $path = $this->getNameInput().'.'.$this->option('extension');

        if ($this->option('path')) {
            return $this->option('path').DIRECTORY_SEPARATOR.$path;
        }

        return $this->viewPath($path);
    }

    protected function getOptions(): array
    {
        $options = parent::getOptions();
        $options[] = ['path', null, InputOption::VALUE_OPTIONAL, 'The location where the view file should be stored'];

        return $options;
    }
}
