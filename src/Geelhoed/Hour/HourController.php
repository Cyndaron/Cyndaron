<?php
namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Location\Location;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\View\Template\ViewHelpers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class HourController extends Controller
{
    #[RouteAttribute('memberList', RequestMethod::GET, UserLevel::ADMIN)]
    public function memberList(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $hour = Hour::fetchById($id);
        if ($hour === null)
        {
            return new Response('Les bestaat niet!', Response::HTTP_NOT_FOUND);
        }
        $page = new MemberListPage($hour);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('byLocationFormatted', RequestMethod::GET, UserLevel::ANONYMOUS, isApiMethod: true)]
    public function byLocationFormatted(QueryBits $queryBits): JsonResponse
    {
        $locationId = $queryBits->getInt(2);
        if ($locationId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $location = Location::fetchById($locationId);
        if ($location === null)
        {
            return new JsonResponse(['error' => 'Locatie bestaat niet!'], Response::HTTP_NOT_FOUND);
        }

        $geelhoedLocation = new \Cyndaron\Geelhoed\Location\Location($location);
        $numbered = $geelhoedLocation->getHoursSortedByWeekday();
        $withNames = [];
        foreach ($numbered as $key => $hours)
        {
            $weekDayName = ViewHelpers::getDutchWeekday($key);
            foreach ($hours as $hour)
            {
                $withNames[$hour->id] = "{$weekDayName}, {$hour->getRange()}";
            }
        }

        return new JsonResponse(['hours' => $withNames]);
    }
}
