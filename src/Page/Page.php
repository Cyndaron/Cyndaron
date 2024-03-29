<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
/** @noinspection PhpIncludeInspection */

namespace Cyndaron\Page;

use Cyndaron\Category\ModelWithCategory;
use Cyndaron\CyndaronInfo;
use Cyndaron\DBAL\Model;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Page\Module\PagePreProcessor;
use Cyndaron\User\User;
use Cyndaron\Util\Link;
use Cyndaron\Util\LinkWithIcon;
use Cyndaron\Util\Setting;
use Cyndaron\View\Template\ViewHelpers;
use function array_key_exists;
use function array_merge;
use function assert;
use function basename;
use function count;
use function dirname;
use function file_exists;
use function is_array;
use function ob_get_clean;
use function Safe\ob_start;
use function sprintf;
use function str_replace;
use function strrchr;
use function substr;

class Page
{
    private const DEFAULT_SCRIPTS = [
        '/vendor/components/jquery/jquery.min.js',
        '/vendor/twbs/bootstrap/dist/js/bootstrap.min.js',
        '/js/cyndaron.js',
    ];
    private const INCLUDES_MAPPING = [
        'extraHead' => 'extra-head',
        'extraBodyStart' => 'extra-body-start',
        'extraBodyEnd' => 'extra-body-end'
    ];

    protected string $title = '';
    /** @var string[] */
    protected array $extraScripts = [];
    /** @var string[] */
    protected array $extraCss = [];
    protected string $websiteName = '';
    protected string $extraBodyClasses = '';

    protected Model|null $model = null;

    protected string $template = '';
    /** @var array<string, mixed> */
    protected array $templateVars = ['contents' => ''];

    /** @var PagePreProcessor[] */
    private static $preProcessors = [];

    public function __construct(string $title)
    {
        $this->title = $title;

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
        $this->templateVars['previewImage'] = '';
        if ($this->model instanceof ModelWithCategory)
        {
            $this->templateVars['previewImage'] = $this->model->getPreviewImage();
        }

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
        $this->templateVars['extraBodyClasses'] = $this->extraBodyClasses;

        foreach (self::INCLUDES_MAPPING as $varName => $filename)
        {
            $this->templateVars[$varName] = '';
            /** @noinspection PhpUndefinedConstantInspection */
            $fullPath = ROOT_DIR . "/$filename.php";
            if (file_exists($fullPath))
            {
                ob_start();
                include $fullPath;
                $this->templateVars[$varName] = ViewHelpers::parseText(ob_get_clean() ?: '');
            }
        }
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
            new LinkWithIcon('/system', 'Systeembeheer', 'cog'),
            new LinkWithIcon('/pagemanager', 'Pagina-overzicht', 'th-list'),
            new LinkWithIcon('/menu-editor', 'Menu bewerken', 'menu-hamburger'),
            new LinkWithIcon('/user/manager', 'Gebruikersbeheer', 'user'),
        ];
        $userMenuItems = [
            new LinkWithIcon('', $_SESSION['username'] ?? '', 'user'),
        ];
        foreach (User::getUserMenuFiltered() as $extraItem)
        {
            $userMenuItems[] = $extraItem->link;
        }
        $userMenuItems[] = new LinkWithIcon('/user/changePassword', 'Wachtwoord wijzigen', 'lock');
        $userMenuItems[] = new LinkWithIcon('/user/logout', 'Uitloggen', 'log-out');

        $vars['userMenuItems'] = $userMenuItems;

        $vars['notifications'] = User::getNotifications();

        $template = new \Cyndaron\View\Template\Template();
        return $template->render('Menu', $vars);
    }

    /**
     * @param array<string, mixed> $vars
     * @throws \Safe\Exceptions\FilesystemException
     * @return string
     */
    public function render(array $vars = []): string
    {
        $this->addTemplateVars($vars);

        $this->renderSkeleton();

        foreach (self::$preProcessors as $processor)
        {
            $processor->process($this);
        }

        $template = new \Cyndaron\View\Template\Template();
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

    /**
     * @return MenuItem[]
     */
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
    public function addTemplateVar(string $varName, mixed $var): void
    {
        $this->templateVars[$varName] = $var;
    }

    /**
     * @param array<string, mixed> $vars
     * @return void
     */
    public function addTemplateVars(array $vars): void
    {
        $this->templateVars = array_merge($this->templateVars, $vars);
    }

    public function addHeadLine(string $line): void
    {
        if (!array_key_exists('extraHeadLines', $this->templateVars))
        {
            $this->templateVars['extraHeadLines'] = [];
        }

        assert(is_array($this->templateVars['extraHeadLines']));
        $this->templateVars['extraHeadLines'][] = $line;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getTemplateVar(string $name): mixed
    {
        return $this->templateVars[$name] ?? null;
    }

    public static function addPreprocessor(PagePreProcessor $processor): void
    {
        self::$preProcessors[] = $processor;
    }
}
