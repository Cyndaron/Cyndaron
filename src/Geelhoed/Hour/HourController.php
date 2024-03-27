<?php
namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

final class HourController extends Controller
{
    public array $getRoutes = [
        'memberList' => ['level' => UserLevel::ADMIN, 'function' => 'memberList'],
    ];

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
        return new Response($page->render());
    }
}
