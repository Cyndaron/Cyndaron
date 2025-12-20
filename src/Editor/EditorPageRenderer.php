<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\Category\ModelWithCategory;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
use Cyndaron\Util\Setting;
use Cyndaron\Util\SettingsRepository;
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Safe\scandir;
use function strlen;
use function sprintf;
use function trim;
use function is_dir;
use function array_filter;
use function str_starts_with;

final class EditorPageRenderer
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly UrlService $urlService,
        private readonly CategoryRepository $categoryRepository,
        private readonly SettingsRepository $settingsRepository,
    ) {
    }

    public function render(Request $request, QueryBits $queryBits, EditorPage $editorPage, EditorVariables $editorVariables): Response
    {
        $editorPage->id = $editorVariables->id;
        $editorPage->prepare();
        $hash = $queryBits->getString(3);
        $hash = strlen($hash) > 20 ? $hash : '';
        $editorPage->addTemplateVar('hash', $hash);
        $editorPage->useBackup = $editorVariables->useBackup;

        $editorPage->title = 'Editor';
        $editorPage->addScript('/vendor/ckeditor/ckeditor/ckeditor.js');
        $editorPage->addScript('/js/editor.js');

        $unfriendlyUrl = new Url('/' . $editorPage::TYPE . '/' . $editorPage->id);
        $friendlyUrl = (string)$this->urlService->toFriendly($unfriendlyUrl);

        if ((string)$unfriendlyUrl === $friendlyUrl)
        {
            $friendlyUrl = '';
        }

        $saveUrl = sprintf($editorPage::SAVE_URL, $editorPage->id ? (string)$editorPage->id : '');
        $editorPage->templateVars['id'] = $editorPage->id;
        $editorPage->templateVars['saveUrl'] = $saveUrl;
        $editorPage->templateVars['articleType'] = $editorPage::TYPE;
        $editorPage->templateVars['hasTitle'] = $editorPage::HAS_TITLE;
        $editorPage->templateVars['hasCategory'] = $editorPage::HAS_CATEGORY;
        $editorPage->templateVars['contentTitle'] = $editorPage->contentTitle;
        $editorPage->templateVars['friendlyUrl'] = trim($friendlyUrl, '/');
        $editorPage->templateVars['friendlyUrlPrefix'] = $request->getSchemeAndHttpHost() . '/';
        $editorPage->templateVars['referrer'] = $request->headers->get('referer');
        $editorPage->templateVars['article'] = $editorPage->content;
        $editorPage->templateVars['model'] = $editorPage->model;
        $editorPage->templateVars['editorHeaderImage'] = ($editorPage->model instanceof ModelWithCategory) ? $editorPage->model->image : '';
        $editorPage->templateVars['editorPreviewImage'] = ($editorPage->model instanceof ModelWithCategory) ? $editorPage->model->previewImage : '';
        $editorPage->templateVars['blurb'] = ($editorPage->model instanceof ModelWithCategory) ? $editorPage->model->blurb : '';

        $editorPage->templateVars['internalLinks'] = $editorVariables->internalLinks;

        if ($editorPage::HAS_CATEGORY)
        {
            $editorPage->templateVars['categories'] = $this->categoryRepository->fetchAllAndSortByName();
            $editorPage->templateVars['selectedCategories'] = [];
            if ($editorPage->id)
            {
                foreach ($editorPage->linkedCategories as $category)
                {
                    $editorPage->templateVars['selectedCategories'][$category->id] = 1;
                }
            }
            else
            {
                $defaultCategoryId = (int)$this->settingsRepository->get('defaultCategory');
                $editorPage->templateVars['selectedCategories'][$defaultCategoryId] = 1;
            }

            $showBreadcrumbs = false;
            if ($editorPage->model instanceof ModelWithCategory)
            {
                $showBreadcrumbs = $editorPage->model->showBreadcrumbs;
            }

            $editorPage->templateVars['showBreadcrumbs'] = $showBreadcrumbs;

            $pageHeaderImages = [];
            $pageHeaderDir = Util::UPLOAD_DIR . '/images/page-header';
            if (is_dir($pageHeaderDir))
            {
                $pageHeaderImages = array_filter(scandir($pageHeaderDir), static function($filename)
                {
                    return !str_starts_with($filename, '.');
                });
            }
            $pagePreviewImages = [];
            $pagePreviewDir = Util::UPLOAD_DIR . '/images/page-preview';
            if (is_dir($pagePreviewDir))
            {
                $pagePreviewImages = array_filter(scandir($pagePreviewDir), static function($filename)
                {
                    return !str_starts_with($filename, '.');
                });
            }

            $editorPage->templateVars['pageHeaderImages'] = $pageHeaderImages;
            $editorPage->templateVars['pagePreviewImages'] = $pagePreviewImages;
        }

        return $this->pageRenderer->renderResponse($editorPage);
    }
}
