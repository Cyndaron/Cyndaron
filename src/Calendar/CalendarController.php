<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends Controller
{
    #[RouteAttribute('overzicht', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function overview(ModuleRegistry $moduleRegistry): Response
    {
        $page = new CalendarIndexPage($moduleRegistry);
        return $this->pageRenderer->renderResponse($page);
    }
}
