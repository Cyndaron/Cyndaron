<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends Controller
{
    protected array $getRoutes = [
        'overzicht' => ['function' => 'overview', 'level' => UserLevel::ANONYMOUS],
    ];

    public function overview(): Response
    {
        $page = new CalendarIndexPage();
        return new Response($page->render());
    }
}
