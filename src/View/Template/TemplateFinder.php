<?php

namespace Cyndaron\View\Template;

use Cyndaron\Module\TemplateRoot;
use function array_key_exists;
use function array_slice;
use function file_exists;
use function explode;
use function count;
use function array_pop;
use function implode;
use function is_string;
use function rtrim;
use function assert;

final class TemplateFinder
{
    private static array $templateRoots = [
        'View' => __DIR__ . '/../',
    ];

    /**
     * Locate actual path to template file (based on current SmartyTools logic)
     *
     * @param string $name
     * @return string|null
     */
    public function path(string $name): ?string
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
    private function searchPath(string $path, string $name): ?string
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
    private function searchSrcAndVendor(string $fullName): ?string
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

            if ($template === null && array_key_exists($module, self::$templateRoots))
            {
                $root = self::$templateRoots[$module];
                $template = $this->searchPath("$root/$pathInModule/templates/", $name);
            }
        }

        return $template;
    }

    public static function addTemplateRoot(TemplateRoot $templateRoot): void
    {
        self::$templateRoots[$templateRoot->name] = rtrim($templateRoot->root, '/');
    }
}
