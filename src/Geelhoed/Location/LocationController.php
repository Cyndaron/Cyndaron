<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Routing\Controller;
use Cyndaron\View\Page;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LocationController extends Controller
{
    protected array $getRoutes = [
        'view' => ['level' => UserLevel::ANONYMOUS, 'function' => 'view'],
        'overview' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overview']
    ];

    public function view(): Response
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $location = Location::loadFromDatabase($id);

        if ($location === null)
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
