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
use Cyndaron\Geelhoed\Reservation\ReservationController;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Module\UserMenu;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use function implode;
use function array_key_exists;

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
            'reservation' => ReservationController::class,
        ];
    }

    public function url(array $linkParts): ?string
    {
        static $staticRoutes = [
            'contest/contestantsList' => 'Overzicht wedstrijdjudoka\'s',
            'contest/manageOverview' => 'Wedstrijdbeheer',
            'contest/myContests' => 'Mijn wedstrijden',
            'contest/overview' => 'Wedstrijden',
            'contest/parentAccounts' => 'Ouderaccounts',
            'location/overview' => 'Leslocaties',
            'reservation/overview' => 'Overzicht reserveringen',
        ];

        $link = implode('/', $linkParts);
        if (array_key_exists($link, $staticRoutes))
        {
            return $staticRoutes[$link];
        }

        if ($linkParts[0] === 'location' && $linkParts[1] === 'view')
        {
            $location = Location::loadFromDatabase((int)$linkParts[2]);
            if ($location === null)
            {
                return null;
            }
            return $location->getName();
        }

        return null;
    }

    public function getUserMenuItems(): array
    {
        $ret = [
            ['label' => 'Wedstrijdbeheer', 'link' => '/contest/manageOverview', 'right' => Contest::RIGHT_MANAGE, 'level' => UserLevel::ADMIN],
            ['label' => 'Overzicht wedstrijdjudoka\'s', 'link' => '/contest/contestantsList', 'right' => Contest::RIGHT_MANAGE, 'level' => UserLevel::ADMIN],
            ['label' => 'Overzicht ouderaccounts', 'link' => '/contest/parentAccounts', 'right' => Contest::RIGHT_MANAGE, 'level' => UserLevel::ADMIN],
            ['label' => 'Overzicht reserveringen', 'link' => '/reservation/overview', 'level' => UserLevel::ADMIN],
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
                $ret[] = ['label' => 'Wedstrijdagenda', 'link' => '/contest/overview', 'level' => UserLevel::LOGGED_IN];
            }
        }

        return $ret;
    }
}
