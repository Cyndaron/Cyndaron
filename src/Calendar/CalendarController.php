<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends Controller
{
    public array $getRoutes = [
        'overzicht' => ['function' => 'overview', 'level' => UserLevel::ANONYMOUS],
    ];

    public function overview(ModuleRegistry $moduleRegistry): Response
    {
        $page = new CalendarIndexPage($moduleRegistry);
        return $this->pageRenderer->renderResponse($page);
    }
}
