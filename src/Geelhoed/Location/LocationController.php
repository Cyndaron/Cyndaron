<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LocationController extends Controller
{
    public array $getRoutes = [
        'details' => ['level' => UserLevel::ANONYMOUS, 'function' => 'view'],
        'overzicht' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overview'],
        'in-stad' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overviewByCity'],
        'op-dag' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overviewByDay'],
        'zoeken' => ['level' => UserLevel::ANONYMOUS, 'function' => 'search'],
        'op-leeftijd' => ['level' => UserLevel::ANONYMOUS, 'function' => 'searchByAge'],
    ];

    public function view(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $location = Location::fetchById($id);

        if ($location === null)
        {
            $page = new SimplePage('Fout bij laden locatie', 'Locatie niet gevonden!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $page = new LocationPage($location);
        return $this->pageRenderer->renderResponse($page);
    }

    public function overview(): Response
    {
        $page = new LocationOverview();
        return $this->pageRenderer->renderResponse($page);
    }

    public function overviewByCity(QueryBits $queryBits): Response
    {
        // Run the value through the slug again to filter out any unwanted characters.
        $city = Util::getSlug($queryBits->getString(2));
        $page = new LocationOverview(LocationFilter::CITY, $city);
        return $this->pageRenderer->renderResponse($page);
    }

    public function overviewByDay(QueryBits $queryBits): Response
    {
        $day = $queryBits->getInt(2);
        $page = new LocationOverview(LocationFilter::DAY, (string)$day);
        return $this->pageRenderer->renderResponse($page);
    }

    public function search(): Response
    {
        $page = new SearchPage();
        return $this->pageRenderer->renderResponse($page);
    }

    public function searchByAge(QueryBits $queryBits): Response
    {
        $age = $queryBits->getInt(2);
        if ($age <= 0)
        {
            $page = new SimplePage('Fout bij zoeken', 'Ongeldige leeftijd opgegeven!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }
        $sportId = $queryBits->getInt(3);
        $sport = Sport::fetchById($sportId);
        if ($sport === null)
        {
            $page = new SimplePage('Fout bij zoeken', 'Ongeldige sport opgegeven!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        $page = new SearchResultsByAgePage($age, $sport);
        return $this->pageRenderer->renderResponse($page);
    }
}
