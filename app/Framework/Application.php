<?php

namespace App\Framework;

class Application extends \LaravelZero\Framework\Application
{
    protected string $buildsPath;

    public function useBuildsPath(string $path): self
    {
        $this->buildsPath = $path;

        return $this;
    }

    public function buildsPath(string $path = ''): string
    {
        return $this->joinPaths($this->buildsPath ?: $this->basePath('builds'), $path);
    }

}
