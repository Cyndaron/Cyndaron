<?php
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\Page\PageRenderer;
use Cyndaron\View\Template\TemplateRenderer;

abstract class Controller
{
    final public function __construct(
        protected readonly string $module,
        protected readonly string $action,
        protected readonly TemplateRenderer $templateRenderer,
        protected readonly PageRenderer $pageRenderer,
    ) {
    }
}
