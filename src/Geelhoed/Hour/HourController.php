<?php
namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class HourController extends Controller
{
    protected array $getRoutes = [
        'memberList' => ['level' => UserLevel::ADMIN, 'function' => 'memberList'],
    ];

    public function memberList(): Response
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $hour = Hour::loadFromDatabase($id);
        $page = new MemberListPage($hour);
        return new Response($page->render());
    }
}
