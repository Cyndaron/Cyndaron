<?php
declare(strict_types=1);

namespace Cyndaron\Base;

use Cyndaron\Routing\Controller;

final class ModuleRegistry
{
    /** @var array<string, class-string<Controller>> */
    public array $controllers = [];

    /** @var array<string, class-string> */
    public array $editorPages = [];

    /** @var array<string, class-string> */
    public array $editorSavePages = [];

    /**
     * @param string $module
     * @param class-string<Controller> $className
     * @return void
     */
    public function addController(string $module, string $className): void
    {
        $this->controllers[$module] = $className;
    }

    /**
     * @param string $module
     * @param class-string $className
     * @return void
     */
    public function addEditorPage(string $module, string $className): void
    {
        $this->editorPages[$module] = $className;
    }

    /**
     * @param string $module
     * @param class-string $className
     * @return void
     */
    public function addEditorSavePage(string $module, string $className): void
    {
        $this->editorSavePages[$module] = $className;
    }
}
