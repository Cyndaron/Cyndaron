<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Geelhoed\Location\LocationRepository;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\View\Template\ViewHelpers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class HourController
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly HourRepository $hourRepository,
        private readonly LocationRepository $locationRepository,
    ) {
    }

    #[RouteAttribute('memberList', RequestMethod::GET, UserLevel::ADMIN)]
    public function memberList(QueryBits $queryBits, MemberRepository $memberRepository): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $hour = $this->hourRepository->fetchById($id);
        if ($hour === null)
        {
            return new Response('Les bestaat niet!', Response::HTTP_NOT_FOUND);
        }
        $page = new MemberListPage($hour, $memberRepository, $this->locationRepository);
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
        $location = $this->locationRepository->fetchById($locationId);
        if ($location === null)
        {
            return new JsonResponse(['error' => 'Locatie bestaat niet!'], Response::HTTP_NOT_FOUND);
        }

        $numbered = $this->locationRepository->getHoursSortedByWeekday($location);
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
