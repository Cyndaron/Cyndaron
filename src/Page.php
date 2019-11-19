<?php /** @noinspection PhpIncludeInspection */

namespace Cyndaron;

use Cyndaron\Category\Category;
use Cyndaron\Menu\MenuItem;
use Cyndaron\User\User;

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
    protected $extraMeta = '';
    protected $title = '';
    protected $titleButtons = null;
    protected $extraScripts = [];
    protected $extraCss = [];
    protected $websiteName = '';
    protected $body = '';
    /** @var Model $model */
    protected $model = null;

    protected $template = '';

    protected $templateVars = [];


    public function __construct(string $title, string $body = '')
    {
        $this->title = $title;
        $this->body = $body;

        $this->updateTemplate();
    }

    protected function updateTemplate()
    {
        if (empty($this->template))
        {
            $rc = new \ReflectionClass(get_called_class());
            $dir = dirname($rc->getFileName()) . '/templates';

            $file = str_replace('.php', '.blade.php', basename($rc->getFileName()));
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

    protected function renderSkeleton()
    {
        $this->websiteName = Setting::get('siteName');
        $this->templateVars['isAdmin'] = User::isAdmin();
        $this->templateVars['websiteName'] = $this->websiteName;
        $this->templateVars['title'] = $this->title;
        $this->templateVars['referrer'] = $_SESSION['referrer'] ?? '';

        $this->templateVars['version'] = CyndaronInfo::ENGINE_VERSION;
        if ($favicon = Setting::get('favicon'))
        {
            $extension = substr(strrchr($favicon, "."), 1);
            $this->templateVars['favicon'] = $favicon;
            $this->templateVars['faviconType'] = "image/$extension";
        }
        $this->templateVars['backgroundColor'] = Setting::get('backgroundColor');
        $this->templateVars['menuColor'] = Setting::get('menuColor');
        $this->templateVars['menuBackground'] = Setting::get('menuBackground');
        $this->templateVars['articleColor'] = Setting::get('articleColor');
        $this->templateVars['accentColor'] = Setting::get('accentColor');

        $this->templateVars['menu'] = $this->renderMenu();

        $jumboContents = Setting::get('jumboContents');
        $this->templateVars['showJumbo'] = $this->isFrontPage() && Setting::get('frontPageIsJumbo') && $jumboContents;
        $this->templateVars['jumboContents'] = Util::parseText($jumboContents);

        $this->templateVars['pageCaptionClasses'] = '';
        if ($this->isFrontPage())
        {
            $this->templateVars['pageCaptionClasses'] = 'voorpagina';
        }

        $this->templateVars['pageCaption'] = $this->generateBreadcrumbs();
        $this->templateVars['titleButtons'] = $this->titleButtons;

        $this->templateVars['extraScripts'] = $this->extraScripts;
        $this->templateVars['extraCss'] = $this->extraCss;

        $this->templateVars['extraHead'] = '';
        if (file_exists(__DIR__ . '/../extra-head.php'))
        {
            ob_start();
            include __DIR__ . '/../extra-head.php';
            $this->templateVars['extraHead'] = ob_get_contents();
            ob_end_clean();
        }

        $this->templateVars['extraBodyStart'] = '';
        if (file_exists(__DIR__ . '/../extra-body-start.php'))
        {
            ob_start();
            include __DIR__ . '/../extra-body-start.php';
            $this->templateVars['extraBodyStart'] = ob_get_contents();
            ob_end_clean();
        }

        $this->templateVars['extraBodyEnd'] = '';
        if (file_exists(__DIR__ . '/../extra-body-end.php'))
        {
            ob_start();
            include __DIR__ . '/../extra-body-end.php';
            $this->templateVars['extraBodyEnd'] = ob_get_contents();
            ob_end_clean();
        }
    }

    public function setExtraMeta(string $extraMeta)
    {
        $this->extraMeta = $extraMeta;
    }

    public function setTitleButtons(string $titleButtons)
    {
        $this->titleButtons = $titleButtons;
    }

    public function showPrePage()
    {
        ob_start();
    }

    public function isFrontPage(): bool
    {
        if ($_SERVER['REQUEST_URI'] === '/')
        {
            return true;
        }
        return false;
    }

    protected function renderMenu()
    {
        $logo = Setting::get('logo');
        $vars = [
            'isLoggedIn' => User::isLoggedIn(),
            'isAdmin' => User::isAdmin(),
            'inverseClass' => (Setting::get('menuTheme') == 'dark') ? 'navbar-dark' : 'navbar-light',
            'navbar' => $logo ? sprintf('<img alt="" src="%s"> ', $logo) : $this->websiteName,
        ];

        $vars['menuItems'] = $this->getMenu();
        $vars['configMenuItems'] = [
            ['link' => '/system', 'title' => 'Systeembeheer', 'icon' => 'cog'],
            ['link' => '/pagemanager', 'title' => 'Pagina-overzicht', 'icon' => 'th-list'],
            ['link' => '/menu-editor', 'title' => 'Menu bewerken', 'icon' => 'menu-hamburger'],
            ['link' => '/user/manager', 'title' => 'Gebruikersbeheer', 'icon' => 'user'],
        ];
        $vars['userMenuItems'] = [
            ['link' => '', 'title' => $_SESSION['naam'] ?? ''],
            ['link' => '/user/logout', 'title' => 'Uitloggen', 'icon' => 'log-out']
        ];

        $vars['notifications'] = User::getNotifications();

        $template = new Template\Template();
        return $template->render('Menu', $vars);
    }

    public function showPostPage()
    {
        $this->templateVars['contents'] = ob_get_contents();
        ob_end_clean();

        $this->renderSkeleton();

        $template = new \Cyndaron\Template\Template();
        echo $template->render($this->template, $this->templateVars);
    }

    public function render(array $vars = [])
    {
        $this->templateVars = array_merge($this->templateVars, $vars);
        $this->showPrePage();
        $this->showBody();
        $this->showPostPage();
    }

    public function addScript($script)
    {
        $this->extraScripts[] = $script;
    }

    public function addCss($script)
    {
        $this->extraCss[] = $script;
    }

    public function getMenu(): array
    {
        if (!User::hasSufficientReadLevel())
        {
            return [];
        }
        return MenuItem::fetchAll([], [], 'ORDER BY priority, id');
    }

    public static function showIfSet($string, $before = '', $after = '')
    {
        if ($string)
        {
            echo $before;
            echo $string;
            echo $after;
        }
    }

    public static function showIfSetAndAdmin($string, $before = '', $after = '')
    {
        if (User::isAdmin() && $string)
        {
            echo $before;
            echo $string;
            echo $after;
        }
    }

    public static function showIfSetAndNotAdmin($string, $before = '', $after = '')
    {
        if (!User::isAdmin() && $string)
        {
            echo $before;
            echo $string;
            echo $after;
        }
    }

    public function showBody(): void
    {
        echo $this->body;
    }

    protected function generateBreadcrumbs(): string
    {
        $title = '';
        $titleParts = [$this->title];
        if ($this->model !== null && $this->model::HAS_CATEGORY)
        {
            $titleParts = [];
            if ($this->model->showBreadcrumbs)
            {
                if ($this->model->categoryId)
                {
                    /** @var Category $category */
                    $category = Category::loadFromDatabase((int)$this->model->categoryId);
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
                $class = ($i === 0 && $count > 1) ? 'breadcrumb-main-item' : 'breadcrumb-item';
                $title .= sprintf('<span class="%s">%s</span>', $class, $titleParts[$i]);
                if ($i !== $count - 1)
                    $title .= '<span class="breadcrumb-separator"> // </span>';
            }
        }

        return $title;
    }

}