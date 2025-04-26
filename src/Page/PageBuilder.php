<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Page;

use Cyndaron\Category\ModelWithCategory;
use Cyndaron\CyndaronInfo;
use Cyndaron\Translation\Translator;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\UserSession;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\Setting;
use Cyndaron\Util\SettingsRepository;
use Cyndaron\View\Renderer\TextRenderer;
use function Safe\ob_start;
use function count;
use function sprintf;
use function strrchr;
use function substr;
use function array_merge;
use function ob_get_clean;
use function assert;
use function dirname;
use function str_replace;
use function basename;
use function strtoupper;
use function file_exists;

final class PageBuilder
{
    private const DEFAULT_SCRIPTS = [
        '/vendor/components/jquery/jquery.min.js',
        '/vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js',
        '/js/cyndaron.js',
    ];
    private const INCLUDES_MAPPING = [
        'extraHead' => 'extra-head',
        'extraBodyStart' => 'extra-body-start',
        'extraBodyEnd' => 'extra-body-end'
    ];

    public function __construct(
        private readonly TextRenderer $textRenderer,
        private readonly CSRFTokenHandler $tokenHandler,
        private readonly Translator $translator,
        private readonly SettingsRepository $sr,
    ) {
    }

    private function generateBreadcrumbs(Page $page): string
    {
        $title = '';
        $titleParts = [$page->title];
        if ($page->model instanceof ModelWithCategory)
        {
            $titleParts = [];
            if ($page->model->showBreadcrumbs)
            {
                $category = $page->category;
                if ($category !== null)
                {
                    $titleParts[] = $category->name;
                }
            }
            $titleParts[] = $page->model->name;
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

    public function addDefaultTemplateVars(Page $page, UserSession $userSession, bool $isFrontPage): void
    {
        $page->addTemplateVar('isAdmin', $userSession->isAdmin());
        $page->addTemplateVar('websiteName', $this->sr->get('siteName'));
        $page->addTemplateVar('title', $page->title);
        $page->addTemplateVar('systemLanguage', $this->sr->get(BuiltinSetting::LANGUAGE));
        $page->addTemplateVar('twitterDescription', '');
        $page->addTemplateVar('previewImage', '');
        $model = $page->model;
        if ($model instanceof ModelWithCategory)
        {
            $page->addTemplateVar('twitterDescription', $model->blurb);
            $page->addTemplateVar('previewImage', $model->getPreviewImage());
        }

        $page->addTemplateVar('version', CyndaronInfo::ENGINE_VERSION);
        $favicon = $this->sr->get('favicon');
        $page->addTemplateVar('favicon', $favicon);
        if ($favicon !== '')
        {
            $dotPosition = strrchr($favicon, '.');
            $extension = $dotPosition !== false ? substr($dotPosition, 1) : '';
            /** @todo Replace with actual mime type check */
            $page->addTemplateVar('faviconType', "image/$extension");
        }

        foreach (['backgroundColor', 'menuColor', 'menuBackground', 'articleColor', 'accentColor'] as $setting)
        {
            $page->addTemplateVar($setting, $this->sr->get($setting));
        }

        $jumboContents = $this->sr->get('jumboContents');
        $page->addTemplateVar('showJumbo', $isFrontPage && $this->sr->get('frontPageIsJumbo') && $jumboContents);
        $page->addTemplateVar('jumboContents', $this->textRenderer->render($jumboContents));

        $page->addTemplateVar('pageCaptionClasses', '');
        if ($isFrontPage)
        {
            $page->addTemplateVar('pageCaptionClasses', 'voorpagina');
        }

        $page->addTemplateVar('pageCaption', $this->generateBreadcrumbs($page));

        $page->addTemplateVar('scripts', array_merge(self::DEFAULT_SCRIPTS, $page->extraScripts));
        $page->addTemplateVar('extraCss', $page->extraCss);
        $page->addTemplateVar('extraBodyClasses', $page->extraBodyClasses);
        $page->addTemplateVar('tokenHandler', $this->tokenHandler);
        $page->addTemplateVar('t', $this->translator);

        foreach (self::INCLUDES_MAPPING as $varName => $filename)
        {
            $page->addTemplateVar($varName, '');
            /** @noinspection PhpUndefinedConstantInspection */
            $fullPath = ROOT_DIR . "/$filename.php";
            if (file_exists($fullPath))
            {
                ob_start();
                include $fullPath;
                $page->addTemplateVar($varName, $this->textRenderer->render(ob_get_clean() ?: ''));
            }
        }
    }

    public function getAutodetectedTemplate(Page $page): string
    {
        $rc = new \ReflectionClass($page);
        $filename = $rc->getFileName();
        assert($filename !== false);
        $dir = dirname($filename) . '/templates';
        $baseFilename = str_replace('.php', '', basename($filename));

        $shortCode = strtoupper($this->sr->get(BuiltinSetting::SHORT_CODE));
        $filenameWithShortcode = $baseFilename . $shortCode . '.blade.php';
        $pathWithShortcode = "$dir/$filenameWithShortcode";
        $filenameWithoutShortcode = $baseFilename . '.blade.php';
        $pathWithoutShortcode = "$dir/$filenameWithoutShortcode";

        if (file_exists($pathWithShortcode))
        {
            return $pathWithShortcode;
        }
        if (file_exists($pathWithoutShortcode))
        {
            return $pathWithoutShortcode;
        }

        return 'Index';
    }

    public function updateTemplate(Page $page): void
    {
        if (!empty($page->template))
        {
            return;
        }

        $page->template = $this->getAutodetectedTemplate($page);
    }
}
