<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

use Cyndaron\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    protected array $getRoutes = [
        'register' => ['level' => UserLevel::ANONYMOUS, 'function' => 'register'],
        'viewRegistrations' => ['level' => UserLevel::ADMIN, 'function' => 'viewRegistrations'],
    ];

    protected function register(): Response
    {
        $id = $this->queryBits->getInt(2);
        $event = Event::loadFromDatabase($id);
        $page = new RegisterPage($event);
        return new Response($page->render());
    }

    protected function viewRegistrations(): Response
    {
        $id = $this->queryBits->getInt(2);
        $event = Event::loadFromDatabase($id);
        $page = new EventOrderOverviewPage($event);
        return new Response($page->render());
    }
}