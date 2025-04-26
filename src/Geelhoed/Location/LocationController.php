<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\DBAL\Connection;
use Cyndaron\Geelhoed\Hour\HourRepository;
use Cyndaron\Geelhoed\Sport\SportRepository;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\ViewHelpers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class LocationController
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly LocationRepository $locationRepository,
    ) {
    }

    #[RouteAttribute('details', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function view(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $location = $this->locationRepository->fetchById($id);

        if ($location === null)
        {
            $page = new SimplePage('Fout bij laden locatie', 'Locatie niet gevonden!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $page = new LocationPage($location, $this->locationRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('overzicht', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function overview(): Response
    {
        $page = new LocationOverview($this->locationRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('in-stad', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function overviewByCity(QueryBits $queryBits): Response
    {
        // Run the value through the slug again to filter out any unwanted characters.
        $city = Util::getSlug($queryBits->getString(2));
        $page = new LocationOverview($this->locationRepository, LocationFilter::CITY, $city);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('op-dag', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function overviewByDay(QueryBits $queryBits): Response
    {
        $day = $queryBits->getInt(2);
        $page = new LocationOverview($this->locationRepository, LocationFilter::DAY, (string)$day);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('zoeken', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function search(Connection $connection, SportRepository $sportRepository): Response
    {
        $dayRecords = $connection->doQueryAndFetchAll('SELECT DISTINCT(day) AS number FROM geelhoed_hours ORDER by number') ?: [];
        $days = [];
        foreach ($dayRecords as $dayRecord)
        {
            $number = (int)$dayRecord['number'];
            $days[$number] = ViewHelpers::getDutchWeekday($number);
        }
        $page = new SearchPage($days, $this->locationRepository, $sportRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('op-leeftijd', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function searchByAge(QueryBits $queryBits, HourRepository $hourRepository, SportRepository $sportRepository): Response
    {
        $age = $queryBits->getInt(2);
        if ($age <= 0)
        {
            $page = new SimplePage('Fout bij zoeken', 'Ongeldige leeftijd opgegeven!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }
        $sportId = $queryBits->getInt(3);
        $sport = $sportRepository->fetchById($sportId);
        if ($sport === null)
        {
            $page = new SimplePage('Fout bij zoeken', 'Ongeldige sport opgegeven!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        $page = new SearchResultsByAgePage($age, $sport, $hourRepository);
        return $this->pageRenderer->renderResponse($page);
    }
}
