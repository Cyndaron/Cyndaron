<?php
namespace Cyndaron\Geelhoed\Member;

use Cyndaron\Controller;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class MemberController extends Controller
{
    protected array $getRoutes = [
        'get' => ['level' => UserLevel::ADMIN, 'function' => 'get'],
    ];

    public function get()
    {
        $id = (int)Request::getVar(2);
        $ret = [];

        if ($member = Member::loadFromDatabase($id))
        {
            $ret = array_merge($member->asArray(), $member->getProfile()->asArray());
        }

        return $ret;
    }

}