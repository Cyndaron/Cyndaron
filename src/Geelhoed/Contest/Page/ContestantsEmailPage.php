<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Page\Page;
use Cyndaron\User\User;
use Cyndaron\User\UserRepository;
use function array_map;
use function implode;

final class ContestantsEmailPage extends Page
{
    public function __construct(MemberRepository $memberRepository, UserRepository $userRepository)
    {
        $this->title = 'E-mailadressen wedstrijdjudoka\'s';
        $independentContestants = $memberRepository->fetchAll(['isContestant = 1', 'id NOT IN (SELECT memberId FROM geelhoed_users_members)']);
        $dependentContestants = $memberRepository->fetchAll(['isContestant = 1', 'id IN (SELECT memberId FROM geelhoed_users_members)']);
        $dependentMemberList = array_map(function(Member $member)
        {
            return (int)$member->id;
        }, $dependentContestants);
        $parents = $userRepository->fetchAll(['id IN (SELECT `userId` FROM `geelhoed_users_members` WHERE `memberId` IN (' . implode(',', $dependentMemberList) . '))']);
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
            $name = $independentContestant->profile->getFullName();
            $email = $independentContestant->getEmail();
            if ($email !== '')
            {
                $pairs[] = ['name' => $name, 'email' => $email];
            }
        }

        $this->addTemplateVars(['emailAddressPairs' => $pairs]);
    }
}
