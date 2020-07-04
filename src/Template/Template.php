<?php

namespace Cyndaron\Template;

use Cyndaron\Util;

class Template
{
    private const COMPILED_DIR = 'cache/template';
    private TemplateFinder $templateFinder;

    public function __construct()
    {
        $this->templateFinder = new TemplateFinder();
    }

    /**
     * @param string $engine
     * @return string
     */
    public function createCacheDir(string $engine): string
    {
        $cacheDir = self::COMPILED_DIR . '/' . $engine;
        Util::createDir($cacheDir);

        return $cacheDir;
    }

    /**
     * @param string $template
     * @param array|null $data
     * @return string
     */
    public function render(string $template, array $data = []): string
    {
        $blade  = new Blade([], $this->createCacheDir('blade'));
        $result = $blade->make($template, $data);

        return $result->render();
    }

    public function templateExists(string $name): bool
    {
        return $this->templateFinder->path($name) !== null;
    }
}
