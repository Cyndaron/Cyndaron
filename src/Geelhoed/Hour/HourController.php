<?php
namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class HourController extends Controller
{
    protected array $getRoutes = [
        'memberList' => ['level' => UserLevel::ADMIN, 'function' => 'memberList'],
    ];

    public function memberList(): Response
    {
        $hour = Hour::loadFromDatabase($this->queryBits->getInt(2));
        $page = new MemberListPage($hour);
        return new Response($page->render());
    }
}