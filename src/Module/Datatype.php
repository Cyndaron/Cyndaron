<?php
declare(strict_types=1);

namespace Cyndaron\Module;

use Closure;
use Cyndaron\DBAL\Model;
use Cyndaron\PageManager\PageManagerTabInterface;

final class Datatype
{
    public function __construct(
        public string $singular = '',
        public string $plural = '',
        /** @var class-string|null */
        public string|null $editorPage = null,
        /** @var class-string|null */
        public string|null $editorSave = null,
        /** @var Closure|class-string<PageManagerTabInterface>|null */
        public Closure|string|null $pageManagerTab = null,
        public string $pageManagerJS = '',
        /** @var class-string<Model>|null */
        public string|null $class = null,
        public Closure|null $modelToUrl = null,
    ) {
    }
}
