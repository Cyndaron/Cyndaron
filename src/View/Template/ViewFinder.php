<?php
namespace Cyndaron\View\Template;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use function strtr;

final class ViewFinder extends FileViewFinder
{
    /**
     * @param TemplateFinder $templateFinder
     * @param Filesystem $filesystem
     * @param string[] $viewPaths
     */
    public function __construct(private readonly TemplateFinder $templateFinder, Filesystem $filesystem, array $viewPaths)
    {
        parent::__construct($filesystem, $viewPaths);
    }
    /**
     * Find the given view in the list of paths.
     *
     * @param  string  $name
     * @phpstan-ignore-next-line
     * @param  array   $paths
     * @throws InvalidArgumentException
     * @return string
     */
    protected function findInPaths($name, $paths): string
    {
        $name = strtr($name, [
            '.' => '/',
            '.blade.php' => '.blade.php',
        ]);
        $path = $this->templateFinder->path($name);

        if ($path !== null)
        {
            return $path;
        }

        throw new InvalidArgumentException("View [$name] not found.");
    }
}
