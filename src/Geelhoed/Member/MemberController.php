<?php
namespace Cyndaron\Geelhoed\Member;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Geelhoed\Hour;
use Cyndaron\Request;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use ReflectionProperty;

class MemberController extends Controller
{
    protected array $getRoutes = [
        'get' => ['level' => UserLevel::ADMIN, 'function' => 'get'],
    ];
    protected array $postRoutes = [
        'save' => ['level' => UserLevel::ADMIN, 'function' => 'save']
    ];

    public function get()
    {
        $id = (int)Request::getVar(2);
        $ret = [];

        if ($member = Member::loadFromDatabase($id))
        {
            $ret = array_merge($member->asArray(), $member->getProfile()->asArray());
            foreach ($member->getHours() as $hour)
            {
                $ret["hour-{$hour->id}"] = true;
            }

            $list = $member->getGraduationList();
            $processedList = array_map(static function (string $line) {
                return "<li>$line</li>";
            }, $list);

            $ret['graduationList'] = implode($processedList);
        }

        return $ret;
    }

    public function save()
    {
        $memberId = (int)Request::post('id');

        if ($memberId > 0) // Edit existing
        {
            $member = Member::loadFromDatabase($memberId);
            $user = $member->getProfile();
        }
        else
        {
            $user = new User();
            $user->level = UserLevel::LOGGED_IN;
            $user->password = '';
            $member = new Member();
        }

        foreach (User::TABLE_FIELDS as $tableField)
        {
            if (!in_array($tableField, ['password', 'level'], true))
            {
                $newValue = User::mangleVarForProperty($tableField, Request::post($tableField));
                if ($tableField === 'email' && $newValue === '')
                    $newValue = null;

                $user->$tableField = $newValue;
            }
        }

        if (!$user->save())
        {
            throw new \Exception('Error saving user record: ' . var_export(DBConnection::errorInfo(), true));
        }

        foreach (Member::TABLE_FIELDS as $tableField)
        {
            if ($tableField === 'joinedAt')
                continue;

            $member->$tableField = Member::mangleVarForProperty($tableField, Request::post($tableField));
        }
        $member->userId = $user->id;
        if (!$member->save())
        {
            throw new \Exception('Error saving member record: ' . var_export(DBConnection::errorInfo(), true));
        }

        $hours = [];
        foreach (Hour::fetchAll() as $hour)
        {
            if (Request::post("hour-{$hour->id}") === '1')
            {
                $hours[] = $hour;
            }
        }
        $member->setHours($hours);

        return ['status' => 'ok'];
    }
}