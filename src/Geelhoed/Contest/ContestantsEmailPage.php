<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\User\User;
use Cyndaron\View\Page;

final class ContestantsEmailPage extends Page
{
    public function __construct()
    {
        parent::__construct('E-mailadressen wedstrijdjudoka\'s');
        $parents = User::fetchAll(['id IN (SELECT `userId` FROM `user_rights` WHERE `right` = ?)'], [Contest::RIGHT_PARENT]);
        $independentContestants = Member::fetchAll(['isContestant = 1', 'gm.id NOT IN (SELECT memberId FROM geelhoed_users_members)'], [], 'ORDER BY lastName,tussenvoegsel,firstName');
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
