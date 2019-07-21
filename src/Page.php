<?php /** @noinspection PhpIncludeInspection */

namespace Cyndaron;

use Cyndaron\Category\Category;
use Cyndaron\Menu\Menu;
use Cyndaron\Menu\MenuItem;
use Cyndaron\User\User;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

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
    const MAIN_TEMPLATE_DIR = __DIR__ . '/templates';
    protected $templateDir = '';
    private $templateDirs = [self::MAIN_TEMPLATE_DIR];
    /** @var $twig Environment */
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
        $this->addTemplateDir($this->templateDir);
        $loader = new FilesystemLoader(array_unique($this->templateDirs));
        $this->twig = new Environment($loader, [
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
        if (empty ($this->templateDir))
        {
            $this->templateDir = dirname($rc->getFileName()) . '/templates';
        }
        if (empty($this->template))
        {
            $file = str_replace('.php', '.twig', basename($rc->getFileName()));
            if (file_exists($this->templateDir . '/' . $file))
            {
                $this->template = $file;
            }
            else
            {
                $this->template = 'index.twig';
            }
        }
    }

    protected function addTemplateDir($dir)
    {
        if (file_exists($dir))
            $this->templateDirs[] = $dir;
    }

    protected function renderSkeleton()
    {
        $this->websiteName = Setting::get('siteName');
        $this->twigVars['isAdmin'] = User::isAdmin();
        $this->twigVars['websiteName'] = $this->websiteName;
        $this->twigVars['title'] = $this->title;
        $this->twigVars['referrer'] = $_SESSION['referrer'] ?? '';

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
        $twigVars['configMenuItems'] = [
            ['link' => '/system', 'title' => 'Systeembeheer', 'icon' => 'cog'],
            ['link' => '/pagemanager', 'title' => 'Pagina-overzicht', 'icon' => 'th-list'],
            ['link' => '/menu-editor', 'title' => 'Menu bewerken', 'icon' => 'menu-hamburger'],
            ['link' => '/user/manager', 'title' => 'Gebruikersbeheer', 'icon' => 'user'],
        ];
        $twigVars['userMenuItems'] = [
            ['link' => '', 'title' => $_SESSION['naam'] ?? ''],
            ['link' => '/user/logout', 'title' => 'Uitloggen', 'icon' => 'log-out']
        ];

        $twigVars['notifications'] = User::getNotifications();

        return $this->twig->render('menu.twig', $twigVars);

    }

    public function showPostPage()
    {
        $this->twigVars['contents'] = ob_get_contents();
        ob_end_clean();

        $this->renderSkeleton();

        echo $this->twig->render($this->template, $this->twigVars);
    }

    public function render(array $vars = [])
    {
        $this->twigVars = array_merge($this->twigVars, $vars);
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

    public function setVar(string $varName, $value)
    {
        $this->twigVars[$varName] = $value;
    }
}