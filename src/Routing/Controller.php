<?php
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\Page\SimplePage;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\DependencyInjectionContainer;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;
use function in_array;
use function is_array;
use function method_exists;
use function Safe\session_destroy;
use function session_start;

abstract class Controller
{
    protected string|null $module = null;
    protected string|null $action = null;
    protected bool $isApiCall = false;

    /** @var array<string, array{function: string, level?: int, right?: string, skipCSRFCheck?: bool }> */
    public array $getRoutes = [];
    /** @var array<string, array{function: string, level?: int, right?: string, skipCSRFCheck?: bool }> */
    public array $postRoutes = [];
    /** @var array<string, array{function: string, level?: int, right?: string, skipCSRFCheck?: bool }> */
    public array $apiGetRoutes = [];
    /** @var array<string, array{function: string, level?: int, right?: string, skipCSRFCheck?: bool }> */
    public array $apiPostRoutes = [];

    public function __construct(string $module, string $action, bool $isApiCall = false)
    {
        $this->module = $module;
        $this->action = $action;
        $this->isApiCall = $isApiCall;
    }
}
