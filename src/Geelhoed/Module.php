<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Calendar\CalendarAppointmentsProvider;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Geelhoed\Clubactie\ClubactieController;
use Cyndaron\Geelhoed\Contest\Controller\ContestController;
use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Geelhoed\Contest\Model\ContestDate;
use Cyndaron\Geelhoed\Hour\HourController;
use Cyndaron\Geelhoed\Location\LocationController;
use Cyndaron\Geelhoed\Member\MemberController;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Geelhoed\Sport\SportController;
use Cyndaron\Geelhoed\Tryout\EditorPage;
use Cyndaron\Geelhoed\Tryout\EditorSave;
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
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserRepository;
use Cyndaron\User\UserSession;
use Cyndaron\Util\Link;
use function array_key_exists;
use function array_merge;
use function implode;

final class Module implements Datatypes, Routes, UrlProvider, UserMenuProvider, Templated, CalendarAppointmentsProvider
{
    public function dataTypes(): array
    {
        return [
            'member' => new Datatype(
                singular: 'Lid',
                plural: 'Leden',
                pageManagerTab: PageManagerTabs::membersTab(...),
                pageManagerJS: '/src/Geelhoed/Member/js/PageManagerTab.js',
            ),
            'contest' => new Datatype(
                singular: 'Wedstrijd',
                plural: 'Wedstrijden',
                pageManagerTab: PageManagerTabs::contestsTab(...),
                pageManagerJS: '/src/Geelhoed/Contest/js/ContestManager.js',
            ),
            'sport' => new Datatype(
                singular: 'Sport',
                plural: 'Sporten',
                pageManagerTab: PageManagerTabs::sportsTab(...),
                pageManagerJS: '/src/Geelhoed/Sport/js/PageManagerTab.js',
            ),
            'tryout' => new Datatype(
                singular: 'Tryout-toernooi',
                plural: 'Tryout-toernooien',
                editorPage: EditorPage::class,
                editorSave: EditorSave::class,
                pageManagerTab: PageManagerTabs::tryoutTab(...),
                pageManagerJS: '/src/Geelhoed/Tryout/js/PageManagerTab.js',
            ),
            'gcaSubscribers' => new Datatype(
                singular: 'Lotenverkoper',
                plural: 'Lotenverkopers',
                pageManagerTab: PageManagerTabs::clubactieTab(...),
                pageManagerJS: '/src/Geelhoed/Clubactie/js/PageManagerTab.js',
            ),
            'orders' => new Datatype(
                singular: 'Webshoporder',
                plural: 'Webshoporders',
                pageManagerTab: PageManagerTabs::ordersTab(...),
            ),
            'products' => new Datatype(
                singular: 'Product',
                plural: 'Producten',
                pageManagerTab: PageManagerTabs::productsTab(...),
            )
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

    public function nameFromUrl(GenericRepository $genericRepository, array $linkParts): string|null
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

    public function getUserMenuItems(): array
    {
        $contestVisibilityCheck1 = function(UserSession $session, UserRepository $repository, MemberRepository $memberRepository): bool
        {
            $profile = $session->getProfile();
            if ($profile === null)
            {
                return false;
            }

            $isContestantParent = $repository->userHasRight($profile, Contest::RIGHT_PARENT);
            $member = $memberRepository->fetch(['userId = ?'], [$profile->id]);
            $isContestant = $member !== null && $member->isContestant;
            return ($isContestant || $isContestantParent);
        };
        $contestVisibilityCheck2 = function(UserSession $session): bool
        {
            $profile = $session->getProfile();
            return ($profile !== null && !$profile->isAdmin());
        };

        return [
            new UserMenuItem(new Link('/contest/manageOverview', 'Wedstrijdbeheer'), UserLevel::ADMIN, Contest::RIGHT_MANAGE),
            new UserMenuItem(new Link('/contest/contestantsEmail', 'E-mailadressen wedstrijdjudoka\'s'), UserLevel::ADMIN, Contest::RIGHT_MANAGE),
            new UserMenuItem(new Link('/contest/contestantsList', 'Overzicht wedstrijdjudoka\'s'), UserLevel::ADMIN, Contest::RIGHT_MANAGE),
            new UserMenuItem(new Link('/contest/parentAccounts', 'Overzicht ouderaccounts'), UserLevel::ADMIN, Contest::RIGHT_MANAGE),
            new UserMenuItem(new Link('/contest/linkContestantsToParentAccounts', 'Wedstrijdjudoka’s linken'), UserLevel::ADMIN, Contest::RIGHT_MANAGE),
            new UserMenuItem(new Link('/reservation/overview', 'Overzicht reserveringen'), UserLevel::ADMIN),
            new UserMenuItem(new Link('/contest/myContests', 'Mijn wedstrijden'), UserLevel::LOGGED_IN, checkVisibility: $contestVisibilityCheck1),
            new UserMenuItem(new Link('/contest/overview', 'Wedstrijdagenda'), UserLevel::LOGGED_IN, checkVisibility: $contestVisibilityCheck1),
            new UserMenuItem(new Link('/pagemanager/gcaSubscribers', 'Lotenverkopers'), UserLevel::ADMIN, WebshopController::RIGHT_MANAGE, checkVisibility: $contestVisibilityCheck2),
            new UserMenuItem(new Link('/pagemanager/orders', 'Webshoporders'), UserLevel::ADMIN, WebshopController::RIGHT_MANAGE, checkVisibility: $contestVisibilityCheck2),
        ];
    }

    public function getTemplateRoot(): TemplateRoot
    {
        return new TemplateRoot('Geelhoed', __DIR__);
    }

    public function getAppointments(GenericRepository $repository): array
    {
        return array_merge($repository->fetchAll(Tryout::class), $repository->fetchAll(ContestDate::class));
    }
}
