<?php
namespace Cyndaron\Geelhoed\Member;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Geelhoed\MemberGraduation;
use Cyndaron\Request;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class MemberController extends Controller
{
    protected array $apiGetRoutes = [
        'get' => ['level' => UserLevel::ADMIN, 'function' => 'get'],
    ];
    protected array $apiPostRoutes = [
        'removeGraduation' => ['level' => UserLevel::ADMIN, 'function' => 'removeGraduation'],
        'save' => ['level' => UserLevel::ADMIN, 'function' => 'save']
    ];

    public function get(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        $ret = [];

        if ($member = Member::loadFromDatabase($id))
        {
            $ret = array_merge($member->asArray(), $member->getProfile()->asArray());
            foreach ($member->getHours() as $hour)
            {
                $ret["hour-{$hour->id}"] = true;
            }

            $list = [];
            foreach($member->getMemberGraduations() as $memberGraduation)
            {
                $graduation = $memberGraduation->getGraduation();
                $description = "{$graduation->getSport()->name}: {$graduation->name} ({$memberGraduation->date})";
                $list[] = sprintf('<li id="member-graduation-%d">%s <a class="btn btn-sm btn-danger remove-member-graduation" data-id="%d"><span class="glyphicon glyphicon-trash"></span></a></li>', $memberGraduation->id, $description, $memberGraduation->id);
            }

            $ret['graduationList'] = implode($list);
        }

        return new JsonResponse($ret);
    }

    public function removeGraduation(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        MemberGraduation::deleteById($id);

        return new JsonResponse();
    }

    public function save(SymfonyRequest $request): JsonResponse
    {
        $memberId = $request->request->getInt('id');

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

        $tableFields = ['username', 'email', 'firstName', 'tussenvoegsel', 'lastName', 'role', 'comments', 'avatar', 'hideFromMemberList', 'gender', 'street', 'houseNumber', 'houseNumberAddition', 'postalCode', 'city', 'dateOfBirth', 'notes'];
        foreach ($tableFields as $tableField)
        {
            $newValue = User::mangleVarForProperty($tableField, Request::post($tableField));
            if ($tableField === 'email' && $newValue === '')
                $newValue = null;

            $user->$tableField = $newValue;
        }

        if (!$user->save())
        {
            throw new \Exception('Error saving user record: ' . var_export(DBConnection::errorInfo(), true));
        }

        foreach (Member::TABLE_FIELDS as $tableField)
        {
            if ($tableField !== 'joinedAt' || Request::post($tableField) !== '')
                $member->$tableField = Member::mangleVarForProperty($tableField, Request::post($tableField));
        }
        $member->userId = $user->id;
        if (!$member->save())
        {
            throw new \Exception('Error saving member record: ' . var_export(DBConnection::errorInfo(), true));
        }

        $newGraduationId = filter_input(INPUT_POST, 'new-graduation-id', FILTER_SANITIZE_NUMBER_INT);
        $newGraduationDate = filter_input(INPUT_POST, 'new-graduation-date', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        if ($newGraduationId && $newGraduationDate)
        {
            $mg = new MemberGraduation();
            $mg->memberId = $member->id;
            $mg->graduationId = $newGraduationId;
            $mg->date = $newGraduationDate;
            $mg->save();
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

        return new JsonResponse();
    }
}