<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\View\Page;
use Cyndaron\User\UserLevel;
use Cyndaron\View\SimplePage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LocationController extends Controller
{
    protected array $getRoutes = [
        'view' => ['level' => UserLevel::ANONYMOUS, 'function' => 'view'],
        'overview' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overview'],
        'search' => ['level' => UserLevel::ANONYMOUS, 'function' => 'search'],
        'searchByAge' => ['level' => UserLevel::ANONYMOUS, 'function' => 'searchByAge'],
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
            $page = new SimplePage('Fout bij laden locatie', 'Locatie niet gevonden!');
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

    public function search(): Response
    {
        $page = new SearchPage();
        return new Response($page->render());
    }

    public function searchByAge(QueryBits $queryBits): Response
    {
        $age = $queryBits->getInt(2);
        if ($age <= 0)
        {
            $page = new SimplePage('Fout bij zoeken', 'Ongeldige leeftijd opgegeven!');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }

        $page = new SearchResultsByAgePage($age);
        return new Response($page->render());
    }
}
