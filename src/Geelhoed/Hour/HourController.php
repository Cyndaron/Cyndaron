<?php
namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
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
}
