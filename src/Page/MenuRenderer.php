<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Page;

use Cyndaron\DBAL\Connection;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Menu\MenuItemRepository;
use Cyndaron\Translation\Translator;
use Cyndaron\Url\UrlService;
use Cyndaron\User\Module\UserMenuItem;
use Cyndaron\User\UserSession;
use Cyndaron\Util\LinkWithIcon;
use Cyndaron\Util\Setting;
use Cyndaron\Util\SettingsRepository;
use Cyndaron\View\Template\TemplateRenderer;
use function sprintf;
use function usort;

final class MenuRenderer
{
    public function __construct(
        private readonly UrlService $urlService,
        private readonly Translator $translator,
        private readonly TemplateRenderer $templateRenderer,
        private readonly Connection $connection,
        private readonly MenuItemRepository $menuItemRepository,
        private readonly SettingsRepository $sr,
    ) {
    }

    /**
     * @return MenuItem[]
     */
    private function getMenu(UserSession $userSession): array
    {
        if (!$userSession->hasSufficientReadLevel())
        {
            return [];
        }

        $ret = $this->menuItemRepository->fetchAll();
        usort($ret, static function(MenuItem $mi1, MenuItem $mi2): int
        {
            $byPriority = (int)$mi1->priority <=> (int)$mi2->priority;
            return $byPriority != 0 ? $byPriority : ($mi1->id <=> $mi2->id);
        });

        return $ret;
    }

    /**
     * @param UserMenuItem[] $userMenu
     */
    public function render(UserSession $userSession, array $userMenu): string
    {
        $websiteName = $this->sr->get('siteName');
        $logo = $this->sr->get('logo');
        $vars = [
            'isLoggedIn' => $userSession->isLoggedIn(),
            'isAdmin' => $userSession->isAdmin(),
            'inverseClass' => ($this->sr->get('menuTheme') === 'dark') ? 'navbar-dark' : 'navbar-light',
            'navbar' => $logo !== '' ? sprintf('<img alt="" src="%s"> ', $logo) : $websiteName,
        ];

        $vars['urlService'] = $this->urlService;
        $vars['menuItems'] = $this->getMenu($userSession);
        $vars['configMenuItems'] = [
            new LinkWithIcon('/system', $this->translator->get('Systeembeheer'), 'cog'),
            new LinkWithIcon('/pagemanager', $this->translator->get('Pagina-overzicht'), 'th-list'),
            new LinkWithIcon('/menu-editor', $this->translator->get('Menu bewerken'), 'menu-hamburger'),
            new LinkWithIcon('/user/manager', $this->translator->get('Gebruikersbeheer'), 'user'),
        ];
        $profile = $userSession->getProfile();
        $userMenuItems = [
            new LinkWithIcon('', $profile ? $profile->username : '', 'user'),
        ];
        foreach ($userMenu as $extraItem)
        {
            $userMenuItems[] = $extraItem->link;
        }
        $userMenuItems[] = new LinkWithIcon('/user/changePassword', $this->translator->get('Wachtwoord wijzigen'), 'lock');
        $userMenuItems[] = new LinkWithIcon('/user/logout', $this->translator->get('Uitloggen'), 'log-out');

        $vars['userMenuItems'] = $userMenuItems;

        $vars['notifications'] = $userSession->getNotifications();
        $vars['t'] = $this->translator;
        $vars['connection'] = $this->connection;
        $vars['menuItemRepository'] = $this->menuItemRepository;

        return $this->templateRenderer->render('Menu', $vars);
    }
}
