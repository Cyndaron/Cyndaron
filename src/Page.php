<?php
/** @noinspection PhpIncludeInspection */

namespace Cyndaron;

use Cyndaron\Menu\MenuItem;
use Cyndaron\Template\ViewHelpers;
use Cyndaron\User\User;

use function Safe\sprintf;
use function Safe\substr;

/*
 * Copyright © 2009-2017, Michael Steenbeek
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */


class Page
{
    public const DEFAULT_SCRIPTS = [
        '/vendor/components/jquery/jquery.min.js',
        '/vendor/twbs/bootstrap/dist/js/bootstrap.min.js',
        '/js/cyndaron.js',
    ];

    protected string $extraMeta = '';
    protected string $title = '';
    protected array $extraScripts = [];
    protected array $extraCss = [];
    protected string $websiteName = '';
    protected string $body = '';

    protected ?Model $model = null;

    protected string $template = '';
    protected array $templateVars = [];

    public function __construct(string $title, string $body = '')
    {
        $this->title = $title;
        $this->body = $body;

        $this->updateTemplate();
    }

    protected function updateTemplate(): void
    {
        if (empty($this->template))
        {
            $rc = new \ReflectionClass(static::class);
            $filename = $rc->getFileName();
            assert($filename !== false);
            $dir = dirname($filename) . '/templates';

            $file = str_replace('.php', '.blade.php', basename($filename));
            $testPath = "$dir/$file";

            if (file_exists($testPath))
            {
                $this->template = $testPath;
            }
            else
            {
                $this->template = 'Index';
            }
        }
    }

    protected function renderSkeleton(): void
    {
        $this->websiteName = Setting::get('siteName');
        $this->templateVars['isAdmin'] = User::isAdmin();
        $this->templateVars['websiteName'] = $this->websiteName;
        $this->templateVars['title'] = $this->title;
        $this->templateVars['referrer'] = $_SESSION['referrer'] ?? '';

        $this->templateVars['version'] = CyndaronInfo::ENGINE_VERSION;
        $favicon = Setting::get('favicon');
        $this->templateVars['favicon'] = $favicon;
        if ($favicon !== '')
        {
            $dotPosition = strrchr($favicon, '.');
            $extension = $dotPosition !== false ? substr($dotPosition, 1) : '';
            /** @todo Replace with actual mime type check */
            $this->templateVars['faviconType'] = "image/$extension";
        }

        foreach (['backgroundColor', 'menuColor', 'menuBackground', 'articleColor', 'accentColor'] as $setting)
        {
            $this->templateVars[$setting] = Setting::get($setting);
        }

        $this->templateVars['menu'] = $this->renderMenu();

        $jumboContents = Setting::get('jumboContents');
        $this->templateVars['showJumbo'] = $this->isFrontPage() && Setting::get('frontPageIsJumbo') && $jumboContents;
        $this->templateVars['jumboContents'] = ViewHelpers::parseText($jumboContents);

        $this->templateVars['pageCaptionClasses'] = '';
        if ($this->isFrontPage())
        {
            $this->templateVars['pageCaptionClasses'] = 'voorpagina';
        }

        $this->templateVars['pageCaption'] = $this->generateBreadcrumbs();

        $this->templateVars['scripts'] = array_merge(self::DEFAULT_SCRIPTS, $this->extraScripts);
        $this->templateVars['extraCss'] = $this->extraCss;

        static $includes = [
            'extraHead' => 'extra-head',
            'extraBodyStart' => 'extra-body-start',
            'extraBodyEnd' => 'extra-body-end'
        ];

        foreach ($includes as $varName => $filename)
        {
            $this->templateVars[$varName] = '';
            if (file_exists(__DIR__ . "/../$filename.php"))
            {
                ob_start();
                include __DIR__ . "/../$filename.php";
                $this->templateVars[$varName] = ob_get_clean();
            }
        }
    }

    public function setExtraMeta(string $extraMeta): void
    {
        $this->extraMeta = $extraMeta;
    }

    public function isFrontPage(): bool
    {
        return $_SERVER['REQUEST_URI'] === '/';
    }

    protected function renderMenu(): string
    {
        $logo = Setting::get('logo');
        $vars = [
            'isLoggedIn' => User::isLoggedIn(),
            'isAdmin' => User::isAdmin(),
            'inverseClass' => (Setting::get('menuTheme') === 'dark') ? 'navbar-dark' : 'navbar-light',
            'navbar' => $logo !== '' ? sprintf('<img alt="" src="%s"> ', $logo) : $this->websiteName,
        ];

        $vars['menuItems'] = $this->getMenu();
        $vars['configMenuItems'] = [
            ['link' => '/system', 'title' => 'Systeembeheer', 'icon' => 'cog'],
            ['link' => '/pagemanager', 'title' => 'Pagina-overzicht', 'icon' => 'th-list'],
            ['link' => '/menu-editor', 'title' => 'Menu bewerken', 'icon' => 'menu-hamburger'],
            ['link' => '/user/manager', 'title' => 'Gebruikersbeheer', 'icon' => 'user'],
        ];
        $vars['userMenuItems'] = [
            ['link' => '', 'title' => $_SESSION['username'] ?? ''],
            ['link' => '/user/logout', 'title' => 'Uitloggen', 'icon' => 'log-out']
        ];

        $vars['notifications'] = User::getNotifications();

        $template = new Template\Template();
        return $template->render('Menu', $vars);
    }

    public function render(array $vars = []): string
    {
        $this->addTemplateVars($vars);

        $this->templateVars['contents'] = $this->body;

        $this->renderSkeleton();

        $template = new \Cyndaron\Template\Template();
        return $template->render($this->template, $this->templateVars);
    }

    public function addScript(string $filename): void
    {
        $this->extraScripts[] = $filename;
    }

    public function addCss(string $filename): void
    {
        $this->extraCss[] = $filename;
    }

    public function getMenu(): array
    {
        if (!User::hasSufficientReadLevel())
        {
            return [];
        }
        return MenuItem::fetchAll([], [], 'ORDER BY priority, id');
    }

    protected function generateBreadcrumbs(): string
    {
        $title = '';
        $titleParts = [$this->title];
        if ($this->model instanceof ModelWithCategory)
        {
            $titleParts = [];
            if ($this->model->showBreadcrumbs)
            {
                $category = $this->model->getFirstCategory();
                if ($category !== null)
                {
                    $titleParts[] = $category->name;
                }
            }
            $titleParts[] = $this->model->name;
        }

        $count = count($titleParts);
        if ($count === 1)
        {
            $title = $titleParts[0];
        }
        else
        {
            for ($i = 0; $i < $count; $i++)
            {
                $class = ($i === 0) ? 'breadcrumb-main-item' : 'breadcrumb-item';
                $title .= sprintf('<span class="%s">%s</span>', $class, $titleParts[$i]);
                if ($i !== $count - 1)
                {
                    $title .= '<span class="breadcrumb-separator"> // </span>';
                }
            }
        }

        return $title;
    }

    /**
     * @param string $varName
     * @param mixed $var
     */
    public function addTemplateVar(string $varName, $var): void
    {
        $this->templateVars[$varName] = $var;
    }

    public function addTemplateVars(array $vars): void
    {
        $this->templateVars = array_merge($this->templateVars, $vars);
    }
}
