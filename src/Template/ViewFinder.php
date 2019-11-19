<?php
namespace Cyndaron\Template;

use Illuminate\View\FileViewFinder;
use InvalidArgumentException;

class ViewFinder extends FileViewFinder
{
    /**
     * Find the given view in the list of paths.
     *
     * @param  string  $name
     * @param  array   $paths
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function findInPaths($name, $paths)
    {
        $name = strtr($name, [
            '.' => '/',
            '.blade.php' => '.blade.php',
        ]);
        $templateFinder = new TemplateFinder();
        $path = $templateFinder->path($name);

        if ($path !== null) {
            return $path;
        }

        throw new InvalidArgumentException("View [$name] not found.");
    }
}