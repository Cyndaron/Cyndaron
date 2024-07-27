<?php
declare(strict_types=1);

namespace Cyndaron\Translation;

use function file_exists;

class Translator
{
    /** @var array<string, string> */
    private readonly array $translations;

    public function __construct(string $code)
    {
        $translations = [];
        $path = ROOT_DIR . '/i18n/' . $code . '.php';
        if (file_exists($path))
        {
            $translations = include $path;
        }

        $this->translations = $translations;
    }

    public function get(string $original): string
    {
        return $this->translations[$original] ?? $original;
    }
}
