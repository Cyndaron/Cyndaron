<?php
namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class HourController extends Controller
{
    protected array $getRoutes = [
        'memberList' => ['level' => UserLevel::ADMIN, 'function' => 'memberList'],
    ];

    public function memberList()
    {
        $hour = Hour::loadFromDatabase(Request::getVar(2));
        new MemberListPage($hour);
    }
}