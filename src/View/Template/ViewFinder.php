<?php
namespace Cyndaron\View\Template;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\FileViewFinder;
use InvalidArgumentException;
use function array_key_exists;
use function array_merge;
use function array_pop;
use function array_slice;
use function assert;
use function count;
use function explode;
use function file_exists;
use function implode;
use function is_string;
use function strtr;

final class ViewFinder extends FileViewFinder
{
    /** @var array<string, string> */
    private readonly array $templateRoots;

    /**
     * @param array<string, string> $templateRoots
     * @param Filesystem $filesystem
     * @param string[] $viewPaths
     */
    public function __construct(array $templateRoots, Filesystem $filesystem, array $viewPaths)
    {
        $this->templateRoots = array_merge(['View' => __DIR__ . '/../'], $templateRoots);
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
        $path = $this->path($name);

        if ($path !== null)
        {
            return $path;
        }

        throw new InvalidArgumentException("View [$name] not found.");
    }

    /**
     * Locate actual path to template file (based on current SmartyTools logic)
     *
     * @param string $name
     * @return string|null
     */
    private function path(string $name): string|null
    {
        // Full path?
        if (file_exists($name))
        {
            return $name;
        }

        // First, look in the global folder.
        $template = $this->searchPath('src/templates/', $name);

        // If the template is not present in the global folder, look in the module templates.
        if ($template === null)
        {
            $template = $this->searchSrcAndVendor($name);
        }

        return $template;
    }

    /**
     * @param string $path
     * @param string $name
     * @return string|null
     */
    private function searchPath(string $path, string $name): string|null
    {
        $baseName = $path . $name;
        $files = [
            $baseName . '.blade.php',
            $baseName . '.html',
            $baseName,
        ];

        foreach ($files as $file)
        {
            if (file_exists($file))
            {
                return $file;
            }
        }

        return null;
    }

    /**
     * @param string $fullName
     * @return string|null
     */
    private function searchSrcAndVendor(string $fullName): string|null
    {
        $template = null;
        $parts = explode('/', $fullName);
        if (count($parts) > 1)
        {
            $name = array_pop($parts);
            assert(is_string($name));
            $module = $parts[0];
            $parts = array_slice($parts, 1);

            $pathInModule = implode('/', $parts);

            $template = $this->searchPath("src/$module/$pathInModule/templates/", $name);
            if ($template === null)
            {
                $template = $this->searchPath("vendor/cyndaron/cyndaron/src/$module/$pathInModule/templates/", $name);
            }

            if ($template === null && array_key_exists($module, $this->templateRoots))
            {
                $root = $this->templateRoots[$module];
                $template = $this->searchPath("$root/$pathInModule/templates/", $name);
            }
        }

        return $template;
    }
}
