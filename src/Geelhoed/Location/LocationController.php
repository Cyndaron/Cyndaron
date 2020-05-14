<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Controller;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class LocationController extends Controller
{
    protected array $getRoutes = [
        'view' => ['level' => UserLevel::ANONYMOUS, 'function' => 'view'],
        'overview' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overview']
    ];

    public function view(): Response
    {
        $id = (int)Request::getVar(2);
        $location = Location::loadFromDatabase($id);

        if (!$location)
        {
            $page = new Page('Fout bij laden locatie', 'Locatie niet gevonden!');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $page = new LocationPage($location);
        return new Response($page->render());
    }

    public function overview(): Response
    {
        $page = new LocationOverview();
        return new Response($page->render());
    }
}