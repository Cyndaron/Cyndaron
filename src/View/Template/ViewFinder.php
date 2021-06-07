<?php
namespace Cyndaron\View\Template;

use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use function strtr;

final class ViewFinder extends FileViewFinder
{
    /**
     * Find the given view in the list of paths.
     *
     * @param  string  $name
     * @param  array   $paths
     * @throws InvalidArgumentException
     * @return string
     *
     */
    protected function findInPaths($name, $paths): string
    {
        $name = strtr($name, [
            '.' => '/',
            '.blade.php' => '.blade.php',
        ]);
        $templateFinder = new TemplateFinder();
        $path = $templateFinder->path($name);

        if ($path !== null)
        {
            return $path;
        }

        throw new InvalidArgumentException("View [$name] not found.");
    }
}
