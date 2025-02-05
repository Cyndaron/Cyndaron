<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class CalendarController
{
    public function __construct(private readonly PageRenderer $pageRenderer)
    {
    }

    #[RouteAttribute('overzicht', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function overview(ModuleRegistry $moduleRegistry, GenericRepository $genericRepository): Response
    {
        $page = new CalendarIndexPage($moduleRegistry, $genericRepository);
        return $this->pageRenderer->renderResponse($page);
    }
}
