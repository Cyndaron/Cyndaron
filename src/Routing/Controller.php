<?php
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\Page\PageRenderer;

abstract class Controller
{
    /** @var array<string, array{function: string, level?: int, right?: string, skipCSRFCheck?: bool }> */
    public array $getRoutes = [];
    /** @var array<string, array{function: string, level?: int, right?: string, skipCSRFCheck?: bool }> */
    public array $postRoutes = [];
    /** @var array<string, array{function: string, level?: int, right?: string, skipCSRFCheck?: bool }> */
    public array $apiGetRoutes = [];
    /** @var array<string, array{function: string, level?: int, right?: string, skipCSRFCheck?: bool }> */
    public array $apiPostRoutes = [];

    public function __construct(
        protected readonly string $module,
        protected readonly string $action,
        protected readonly bool $isApiCall,
        protected readonly PageRenderer $pageRenderer,
    ) {
    }
}
