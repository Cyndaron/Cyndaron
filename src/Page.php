<?php /** @noinspection PhpIncludeInspection */

namespace Cyndaron;

use Cyndaron\Category\Category;
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

    protected $template = 'index.twig';
    const MAIN_TEMPLATE_DIR = __DIR__ . '/templates';
    protected $templateDir = '';
    protected $twig = null;
    protected $twigVars = [];

    public function __construct(string $title, string $body = '')
    {
        $this->title = $title;
        $this->body = $body;

        $this->setupTwig();
    }

    protected function setupTwig()
    {
        $this->updateTemplate();
        $templatePaths = [self::MAIN_TEMPLATE_DIR];
        if (file_exists($this->templateDir)) {
            $templatePaths[] = $this->templateDir;
        }
        $loader = new \Twig\Loader\FilesystemLoader($templatePaths);
        $this->twig = new \Twig\Environment($loader, [
            'auto_reload' => true,
            'cache' => __DIR__ . '/../cache/twig',
        ]);
        $ext = new TwigHelper();
        foreach ($ext->getFunctions() as $function)
        {
            $this->twig->addFunction($function);
        }
        foreach ($ext->getFilters() as $filter)
        {
            $this->twig->addFilter($filter);
        }
    }

    protected function updateTemplate()
    {
        $rc = new \ReflectionClass(get_called_class());
        $this->templateDir = dirname($rc->getFileName()) . '/templates';
        $file = str_replace('.php', '.twig', basename($rc->getFileName()));
        if (file_exists($this->templateDir . '/' . $file)) {
            $this->template = $file;
        }
    }

    protected function renderSkeleton()
    {
        $this->websiteName = Setting::get('siteName');
        $this->twigVars['isAdmin'] = User::isAdmin();
        $this->twigVars['websiteName'] = $this->websiteName;
        $this->twigVars['title'] = $this->title;

        $this->twigVars['version'] = CyndaronInfo::ENGINE_VERSION;
        if ($favicon = Setting::get('favicon'))
        {
            $extension = substr(strrchr($favicon, "."), 1);
            $this->twigVars['favicon'] = $favicon;
            $this->twigVars['faviconType'] = "image/$extension";
        }
        $this->twigVars['backgroundColor'] = Setting::get('backgroundColor');
        $this->twigVars['menuColor'] = Setting::get('menuColor');
        $this->twigVars['articleColor'] = Setting::get('articleColor');
        $this->twigVars['accentColor'] = Setting::get('accentColor');



        $this->twigVars['menu'] = $this->renderMenu();

        $jumboContents = Setting::get('jumboContents');
        $this->twigVars['showJumbo'] = $this->isFrontPage() && Setting::get('frontPageIsJumbo') && $jumboContents;
        $this->twigVars['jumboContents'] = $jumboContents;

        $this->twigVars['pageCaptionClasses'] = '';
        if ($this->isFrontPage())
        {
            $this->twigVars['pageCaptionClasses'] = 'voorpagina';
        }

        $this->twigVars['pageCaption'] = $this->generateBreadcrumbs();
        $this->twigVars['titleButtons'] = $this->titleButtons;

        $this->twigVars['extraScripts'] = $this->extraScripts;
        $this->twigVars['extraCss'] = $this->extraCss;

        $this->twigVars['extraHead'] = '';
        if (file_exists(__DIR__ . '/../extra-head.php'))
        {
            ob_start();
            include __DIR__ . '/../extra-head.php';
            $this->twigVars['extraHead'] = ob_get_contents();
            ob_end_clean();
        }

        $this->twigVars['extraBodyStart'] = '';
        if (file_exists(__DIR__ . '/../extra-body-start.php'))
        {
            ob_start();
            include __DIR__ . '/../extra-body-start.php';
            $this->twigVars['extraBodyStart'] = ob_get_contents();
            ob_end_clean();
        }

        $this->twigVars['extraBodyEnd'] = '';
        if (file_exists(__DIR__ . '/../extra-body-end.php'))
        {
            ob_start();
            include __DIR__ . '/../extra-body-end.php';
            $this->twigVars['extraBodyEnd'] = ob_get_contents();
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
        $twigVars = [
            'isLoggedIn' => User::isLoggedIn(),
            'isAdmin' => User::isAdmin(),
            'inverseClass' => (Setting::get('menuTheme') == 'dark') ? 'navbar-dark' : 'navbar-light',
            'navbar' => $logo ? sprintf('<img alt="" src="%s"> ', $logo) : $this->websiteName,
        ];

        $twigVars['menuItems'] = $this->getMenu();
        $twigVars['configMenu'] = $this->renderMenuDropdown('<span class="glyphicon glyphicon-wrench"></span>', [
            ['link' => '/system', 'title' => '<span class="glyphicon glyphicon-cog"></span>&nbsp; Systeembeheer'],
            ['link' => '/pagemanager', 'title' => '<span class="glyphicon glyphicon-th-list"></span>&nbsp; Pagina-overzicht'],
            ['link' => '/menu-editor', 'title' => '<span class="glyphicon glyphicon-menu-hamburger"></span>&nbsp; Menu bewerken'],
            ['link' => '/user/manager', 'title' => '<span class="glyphicon glyphicon-user"></span>&nbsp; Gebruikersbeheer'],
        ]);
        $twigVars['userMenu'] = $this->renderMenuDropdown('<span class="glyphicon glyphicon-user"></span>', [
            ['link' => '', 'title' => $_SESSION['naam'] ?? ''],
            ['link' => '/user/logout', 'title' => '<span class="glyphicon glyphicon-log-out"></span> Uitloggen']
        ]);

        $twigVars['notifications'] = User::getNotifications();

        return $this->twig->render('menu.twig', $twigVars);

    }

    private function menuItemIsCurrentPage(string $menuItem): bool
    {
        // Vergelijking na || betekent testen of de hoofdurl is opgevraagd
        if ($menuItem == basename(substr($_SERVER['REQUEST_URI'], 1)) || ($menuItem == '/' && $_SERVER['REQUEST_URI'] === '/'))
        {
            return true;
        }

        return false;
    }

    public function showPostPage()
    {
        $this->twigVars['contents'] = ob_get_contents();
        ob_end_clean();

        $this->renderSkeleton();

        echo $this->twig->render($this->template, $this->twigVars);
    }

    public function render()
    {

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
        $menu = DBConnection::doQueryAndFetchAll('SELECT * FROM menu ORDER BY priority, id;');
        $menuitems = [];
        $frontPage = Setting::get('frontPage');

        foreach ($menu as $menuitem)
        {
            $url = new Url($menuitem['link']);

            if ($menuitem['alias'])
            {
                $menuitem['name'] = strtr($menuitem['alias'], [' ' => '&nbsp;']);
            }
            else
            {
                $menuitem['name'] = $url->getPageTitle();
            }

            if ($menuitem['link'] == $frontPage)
            {
                $menuitem['link'] = '/';
            }
            // For dropdowns, this is not necessary and it makes detection harder down the line.
            elseif (!$menuitem['isDropdown'])
            {
                $menuitem['link'] = $url->getFriendly();
            }
            $menuitem['isCurrent'] = $this->menuItemIsCurrentPage($menuitem['link']);
            $menuitem['isCategoryDropdown'] = strpos($menuitem['link'], '/category/') === 0 && $menuitem['isDropdown'];
            if ($menuitem['isCategoryDropdown'])
            {
                $menuitem['categoryDropdown'] = $this->renderCategoryDropdown($menuitem);
            }
            $menuitems[] = $menuitem;
        }
        return $menuitems;
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

    protected function renderCategoryDropdown(array $menuitem)
    {
        $id = intval(str_replace('/category/', '', $menuitem['link']));
        $pagesInCategory = DBConnection::doQueryAndFetchAll("
            SELECT * FROM
            (
                SELECT 'sub' AS type, id, name FROM subs WHERE categoryId=?
                UNION
                SELECT 'photoalbum' AS type, id, name FROM photoalbums WHERE categoryId=?
                UNION
                SELECT 'category' AS type, id, name FROM categories WHERE categoryId=?
            ) AS one
            ORDER BY name ASC;",
            [$id, $id, $id]);

        $items = [];
        foreach ($pagesInCategory as $pagina)
        {
            $url = new Url(sprintf('/%s/%d', $pagina['type'], $pagina['id']));
            $link = $url->getFriendly();
            $items[] = ['link' => $link, 'title' => $pagina['name']];
        }

        return $this->renderMenuDropdown($menuitem['name'], $items);
    }

    protected function renderMenuDropdown(string $title, array $items)
    {
        return $this->twig->render('menuDropdown.twig', ['dropdownTitle' => $title, 'items' => $items]);
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