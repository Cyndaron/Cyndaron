<?php

namespace Cyndaron\Template;

final class TemplateFinder
{
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
            $module = implode('/', $parts);

            $template = $this->searchPath('src/' . $module . '/templates/', $name);
            // If the template is not present in the module templates, look in the vendor packages.
            if ($template === null)
            {
                $template = $this->searchPath('vendor/' . $module . '/templates/', $name);
            }
        }

        return $template;
    }
}
