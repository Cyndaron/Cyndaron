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
            ['label' => 'Wedstrijdbeheer', 'link' => '/contest/manageOverview' , 'right' => Contest::RIGHT, 'level' => UserLevel::ADMIN],
        ];

        /*$profile = User::fromSession();
        if ($profile !== null)
        {
            $member = Member::fetch(['userId = ?'], [$profile->id]);
            if ($member !== null && $member->isContestant)
            {
                $ret[] = ['label' => 'Mijn wedstrijden'];
            }
        }*/

        return $ret;
    }
}
