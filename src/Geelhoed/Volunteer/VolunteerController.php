<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Volunteer;

use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class VolunteerController extends Controller
{
    protected array $getRoutes = [
        'subscribe' => ['function' => 'subscribe', 'level' => UserLevel::ANONYMOUS],
    ];

    public function subscribe(): Response
    {
        $page = new SubscriptionPage();
        return new Response($page->render());
    }
}
