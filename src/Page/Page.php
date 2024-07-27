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
use Cyndaron\Page\Module\PagePreProcessor;
use Cyndaron\Translation\Translator;
use Cyndaron\Url\UrlService;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\Module\UserMenuItem;
use Cyndaron\User\UserSession;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\Setting;
use Cyndaron\View\Renderer\TextRenderer;
use Cyndaron\View\Template\TemplateRenderer;
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
use function strtoupper;
use function substr;

class Page implements Pageable
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
    public array $extraScripts = [];
    /** @var string[] */
    public array $extraCss = [];
    protected string $websiteName = '';
    protected string $extraBodyClasses = '';

    protected Model|null $model = null;

    protected string $template = '';
    /** @var array<string, mixed> */
    public array $templateVars = ['contents' => ''];

    public function __construct(string $title)
    {
        $this->title = $title;

        $this->updateTemplate();
    }

    protected function updateTemplate(): void
    {
        if (!empty($this->template))
        {
            return;
        }

        $rc = new \ReflectionClass(static::class);
        $filename = $rc->getFileName();
        assert($filename !== false);
        $dir = dirname($filename) . '/templates';
        $baseFilename = str_replace('.php', '', basename($filename));

        $shortCode = strtoupper(Setting::get(BuiltinSetting::SHORT_CODE));
        $filenameWithShortcode = $baseFilename . $shortCode . '.blade.php';
        $pathWithShortcode = "$dir/$filenameWithShortcode";
        $filenameWithoutShortcode = $baseFilename . '.blade.php';
        $pathWithoutShortcode = "$dir/$filenameWithoutShortcode";

        if (file_exists($pathWithShortcode))
        {
            $this->template = $pathWithShortcode;
        }
        elseif (file_exists($pathWithoutShortcode))
        {
            $this->template = $pathWithoutShortcode;
        }
        else
        {
            $this->template = 'Index';
        }
    }

    protected function renderSkeleton(TextRenderer $textRenderer, CSRFTokenHandler $tokenHandler, UserSession $userSession, bool $isFrontPage): void
    {
        $this->websiteName = Setting::get('siteName');
        $this->templateVars['isAdmin'] = $userSession->isAdmin();
        $this->templateVars['websiteName'] = $this->websiteName;
        $this->templateVars['title'] = $this->title;
        $this->templateVars['systemLanguage'] = Setting::get(BuiltinSetting::LANGUAGE);
        $this->templateVars['twitterDescription'] = '';
        $this->templateVars['previewImage'] = '';
        if ($this->model instanceof ModelWithCategory)
        {
            $this->templateVars['twitterDescription'] = $this->model->blurb;
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

        $jumboContents = Setting::get('jumboContents');
        $this->templateVars['showJumbo'] = $isFrontPage && Setting::get('frontPageIsJumbo') && $jumboContents;
        $this->templateVars['jumboContents'] = $textRenderer->render($jumboContents);

        $this->templateVars['pageCaptionClasses'] = '';
        if ($isFrontPage)
        {
            $this->templateVars['pageCaptionClasses'] = 'voorpagina';
        }

        $this->templateVars['pageCaption'] = $this->generateBreadcrumbs();

        $this->templateVars['scripts'] = array_merge(self::DEFAULT_SCRIPTS, $this->extraScripts);
        $this->templateVars['extraCss'] = $this->extraCss;
        $this->templateVars['extraBodyClasses'] = $this->extraBodyClasses;
        $this->templateVars['tokenHandler'] = $tokenHandler;

        foreach (self::INCLUDES_MAPPING as $varName => $filename)
        {
            $this->templateVars[$varName] = '';
            /** @noinspection PhpUndefinedConstantInspection */
            $fullPath = ROOT_DIR . "/$filename.php";
            if (file_exists($fullPath))
            {
                ob_start();
                include $fullPath;
                $this->templateVars[$varName] = $textRenderer->render(ob_get_clean() ?: '');
            }
        }
    }

    /**
     * @param PagePreProcessor[] $pageProcessors
     * @param array<string, mixed> $vars
     */
    public function render(TemplateRenderer $templateRenderer, TextRenderer $textRenderer, CSRFTokenHandler $tokenHandler, UserSession $userSession, array $pageProcessors, bool $isFrontPage, array $vars = []): string
    {
        $this->addTemplateVars($vars);

        $this->renderSkeleton($textRenderer, $tokenHandler, $userSession, $isFrontPage);

        foreach ($pageProcessors as $processor)
        {
            $processor->process($this);
        }

        return $templateRenderer->render($this->template, $this->templateVars);
    }

    public function addScript(string $filename): void
    {
        $this->extraScripts[] = $filename;
    }

    public function addCss(string $filename): void
    {
        $this->extraCss[] = $filename;
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

    public function toPage(): Page
    {
        return $this;
    }
}
