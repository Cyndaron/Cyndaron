<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Calendar\CalendarAppointmentsProvider;
use Cyndaron\Geelhoed\Clubactie\ClubactieController;
use Cyndaron\Geelhoed\Contest\Contest;
use Cyndaron\Geelhoed\Contest\ContestController;
use Cyndaron\Geelhoed\Contest\ContestDate;
use Cyndaron\Geelhoed\Hour\HourController;
use Cyndaron\Geelhoed\Location\LocationController;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberController;
use Cyndaron\Geelhoed\Sport\SportController;
use Cyndaron\Geelhoed\Tryout\Tryout;
use Cyndaron\Geelhoed\Tryout\TryoutController;
use Cyndaron\Geelhoed\Volunteer\VolunteerController;
use Cyndaron\Geelhoed\Webshop\WebshopController;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\Templated;
use Cyndaron\Module\TemplateRoot;
use Cyndaron\Module\UrlProvider;
use Cyndaron\User\Module\UserMenuItem;
use Cyndaron\User\Module\UserMenuProvider;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Link;
use function array_key_exists;
use function array_merge;
use function implode;

final class Module implements Datatypes, Routes, UrlProvider, UserMenuProvider, Templated, CalendarAppointmentsProvider
{
    public function dataTypes(): array
    {
        return [
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
            'sport' => Datatype::fromArray([
                'singular' => 'Sport',
                'plural' => 'Sporten',
                'pageManagerTab' => PageManagerTabs::class . '::sportsTab',
                'pageManagerJS' => '/src/Geelhoed/Sport/js/PageManagerTab.js',
            ]),
            'tryout' => Datatype::fromArray([
                'singular' => 'Tryout-toernooi',
                'plural' => 'Tryout-toernooien',
                'pageManagerTab' => PageManagerTabs::class . '::tryoutTab',
                'pageManagerJS' => '/src/Geelhoed/Tryout/js/PageManagerTab.js',
            ])
        ];
    }

    public function routes(): array
    {
        return [
            'hour' => HourController::class,
            'locaties' =>  LocationController::class,
            'member' => MemberController::class,
            'contest' => ContestController::class,
            'vrijwilligers' => VolunteerController::class,
            'tryout' => TryoutController::class,
            'sport' => SportController::class,
            'clubactie' => ClubactieController::class,
            'webwinkel' => WebshopController::class,
        ];
    }

    public function url(array $linkParts): string|null
    {
        static $staticRoutes = [
            'contest/contestantsEmail' => 'E-mailadressen wedstrijdjudoka\'s',
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

        return null;
    }

    public function getUserMenuItems(?User $profile): array
    {
        $ret = [
            new UserMenuItem(new Link('/contest/manageOverview', 'Wedstrijdbeheer'), UserLevel::ADMIN, Contest::RIGHT_MANAGE),
            new UserMenuItem(new Link('/contest/contestantsEmail', 'E-mailadressen wedstrijdjudoka\'s'), UserLevel::ADMIN, Contest::RIGHT_MANAGE),
            new UserMenuItem(new Link('/contest/contestantsList', 'Overzicht wedstrijdjudoka\'s'), UserLevel::ADMIN, Contest::RIGHT_MANAGE),
            new UserMenuItem(new Link('/contest/parentAccounts', 'Overzicht ouderaccounts'), UserLevel::ADMIN, Contest::RIGHT_MANAGE),
            new UserMenuItem(new Link('/contest/linkContestantsToParentAccounts', 'Wedstrijdjudoka’s linken'), UserLevel::ADMIN, Contest::RIGHT_MANAGE),
            new UserMenuItem(new Link('/reservation/overview', 'Overzicht reserveringen'), UserLevel::ADMIN),
        ];

        if ($profile !== null)
        {
            $isContestantParent = $profile->hasRight(Contest::RIGHT_PARENT);
            $member = Member::fetch(['userId = ?'], [$profile->id]);
            $isContestant = $member !== null && $member->isContestant;

            if ($isContestant || $isContestantParent)
            {
                $ret[] = new UserMenuItem(new Link('/contest/myContests', 'Mijn wedstrijden'), UserLevel::LOGGED_IN);
                $ret[] = new UserMenuItem(new Link('/contest/overview', 'Wedstrijdagenda'), UserLevel::LOGGED_IN);
            }
        }

        return $ret;
    }

    public function getTemplateRoot(): TemplateRoot
    {
        return new TemplateRoot('Geelhoed', __DIR__);
    }

    public function getAppointments(): array
    {
        return array_merge(Tryout::fetchAll(), ContestDate::fetchAll());
    }
}
