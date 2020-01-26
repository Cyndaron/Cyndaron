<?php

namespace Cyndaron\Template;

use Cyndaron\Util;

class Template
{
    private string $compiledDir = 'cache/template';
    private array $data = [];
    private TemplateFinder $templateFinder;

    public function __construct()
    {
        $this->templateFinder  = new TemplateFinder();
    }

    /**
     * @param string $file
     * @deprecated
     */
    public function display(string $file)
    {
        echo $this->render($file, $this->data);
    }

    /**
     * @param string $engine
     * @return string
     */
    public function createCacheDir(string $engine): string
    {
        $cacheDir = $this->compiledDir . '/' . $engine;
        Util::createDir($cacheDir);

        return $cacheDir;
    }

    /**
     * @param string $template
     * @param array|null $data
     * @return string
     */
    public function render(string $template, array $data = null)
    {
        $data = $data ?: $this->data;

        $blade  = new Blade([], $this->createCacheDir('blade'));
        $result = $blade->make($template, $data);

        return $result->render();
    }

    public function templateExists($name)
    {
        return $this->templateFinder->path($name) !== null;
    }
}