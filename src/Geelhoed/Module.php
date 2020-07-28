<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Contest\Contest;
use Cyndaron\Geelhoed\Contest\ContestController;
use Cyndaron\Geelhoed\Hour\HourController;
use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Geelhoed\Location\LocationController;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberController;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Module\UserMenu;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

final class Module implements Datatypes, Routes, UrlProvider, UserMenu
{
    /**
     * @return Datatype[]
     */
    public function dataTypes(): array
    {
        return [
            'location' => Datatype::fromArray([
                'singular' => 'Locatie',
                'plural' => 'Locaties',
                'pageManagerTab' => PageManagerTabs::class . '::locationsTab',
            ]),
            'member' => Datatype::fromArray([
                'singular' => 'Lid',
                'plural' => 'Leden',
                'pageManagerTab' => PageManagerTabs::class . '::membersTab',
                'pageManagerJS' => '/src/Geelhoed/Member/js/PageManagerTab.js',
            ]),
            'contest' => Datatype::fromArray([
                'singular' => 'Wedstrijd',
                'plural' => 'Wedstrijden',
                'pageManagerTab' => PageManagerTabs::class . '::contestsTab',
                'pageManagerJS' => '/src/Geelhoed/Contest/js/ContestManager.js',
            ]),
        ];
    }

    public function routes(): array
    {
        return [
            'hour' => HourController::class,
            'location' =>  LocationController::class,
            'member' => MemberController::class,
            'contest' => ContestController::class,
        ];
    }

    public function url(array $linkParts): ?string
    {
        switch ($linkParts[0])
        {
            case 'contest':
                switch ($linkParts[1])
                {
                    case 'contestantsList':
                        return 'Overzicht wedstrijdjudoka\'s';
                    case 'manageOverview':
                        return 'Wedstrijdbeheer';
                    case 'myContests':
                        return 'Mijn wedstrijden';
                    case 'overview':
                        return 'Wedstrijden';
                    case 'parentAccounts':
                        return 'Ouderaccounts';
                }
                break;
            case 'location':
                switch ($linkParts[1])
                {
                    case 'overview':
                        return 'Leslocaties';
                    case 'view':
                        $location = Location::loadFromDatabase((int)$linkParts[2]);
                        if ($location === null)
                        {
                            return null;
                        }
                        return $location->getName();
                }
        }

        return null;
    }

    public function getUserMenuItems(): array
    {
        $ret = [
            ['label' => 'Wedstrijdbeheer', 'link' => '/contest/manageOverview', 'right' => Contest::RIGHT_MANAGE, 'level' => UserLevel::ADMIN],
            ['label' => 'Overzicht wedstrijdjudoka\'s', 'link' => '/contest/contestantsList', 'right' => Contest::RIGHT_MANAGE, 'level' => UserLevel::ADMIN],
            ['label' => 'Overzicht ouderaccounts', 'link' => '/contest/parentAccounts', 'right' => Contest::RIGHT_MANAGE, 'level' => UserLevel::ADMIN],
        ];

        $profile = User::fromSession();
        if ($profile !== null)
        {
            $isContestantParent = $profile->hasRight(Contest::RIGHT_PARENT);
            $member = Member::fetch(['userId = ?'], [$profile->id]);
            $isContestant = $member !== null && $member->isContestant;

            if ($isContestant || $isContestantParent)
            {
                $ret[] = ['label' => 'Mijn wedstrijden', 'link' => '/contest/myContests', 'level' => UserLevel::LOGGED_IN];
            }
        }

        return $ret;
    }
}
