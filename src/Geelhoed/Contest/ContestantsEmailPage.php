<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Page\Page;
use Cyndaron\User\User;
use function array_map;
use function implode;

final class ContestantsEmailPage extends Page
{
    public function __construct()
    {
        $this->title = 'E-mailadressen wedstrijdjudoka\'s';
        $independentContestants = Member::fetchAll(['isContestant = 1', 'id NOT IN (SELECT memberId FROM geelhoed_users_members)']);
        $dependentContestants = Member::fetchAll(['isContestant = 1', 'id IN (SELECT memberId FROM geelhoed_users_members)']);
        $dependentMemberList = array_map(function(Member $member)
        {
            return (int)$member->id;
        }, $dependentContestants);
        $parents = User::fetchAll(['id IN (SELECT `userId` FROM `geelhoed_users_members` WHERE `memberId` IN (' . implode(',', $dependentMemberList) . '))']);
        $pairs = [];

        foreach ($parents as $parent)
        {
            if (!empty($parent->email))
            {
                $pairs[] = ['name' => $parent->getFullName(), 'email' => $parent->email];
            }
        }
        foreach ($independentContestants as $independentContestant)
        {
            $name = $independentContestant->getProfile()->getFullName();
            $email = $independentContestant->getEmail();
            if ($email !== '')
            {
                $pairs[] = ['name' => $name, 'email' => $email];
            }
        }

        $this->addTemplateVars(['emailAddressPairs' => $pairs]);
    }
}
